<?php

namespace app\admin\controller\uniprint;

use app\common\controller\Backend;
use think\Exception;
use addons\uniprint\library\feie\HttpClient;

/**
 * 打印机管理
 *
 * @icon fa fa-circle-o
 */
class Printer extends Backend
{

    /**
     * Printer模型对象
     * @var \app\admin\model\uniprint\Printer
     */
    protected $model = null;

    protected $configs = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\uniprint\Printer;

        $this->configs = get_addon_config('uniprint');
        $configs = (array)get_addon_fullconfig('uniprint');
        $brandList = [];
        foreach ($configs as $config) {
            $brandList[$config['name']] = $config['title'];
        }
        $this->assign('brandList', $brandList);
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 测试打印
     */
    public function printing()
    {
        $id = $this->request->get('id');
        /**
         * @var \app\admin\model\uniprint\Printer $printer
         */
        $printer = $this->model->get($id);
        if (empty($printer)) {
            $this->error('打印机不存在');
        }

        $template = '测试打印';
        switch ($printer->brand_id) {
            case 'zhongwu':

                break;
            case 'yilianyun':

                break;
            case 'feie':
                //$template = '测试打印';
                // 这里绑定一下
                $time = time();         //请求时间
                $msgInfo = array(
                    'user' => $this->configs['feie']['USER'],
                    'stime' => $time,
                    'sig' => sha1($this->configs['feie']['USER'] . $this->configs['feie']['UKEY'] . $time),
                    'apiname' => 'Open_printerAddlist',
                    'printerContent' => $printer->device_id . '#' . $printer->device_secret . '#' . $printer->name . '#' . $printer->card
                );
                $client = new HttpClient($this->configs['feie']['IP'], $this->configs['feie']['PORT']);
                if (!$client->post($this->configs['feie']['PATH'], $msgInfo)) {
                    echo 'error';
                } else {
                    $result = $client->getContent();
                    $result = json_decode($result, true);
                    if (!empty($result['data']['no'])) {
                        // 不管是否错误都不报错 仅作为绑定
                        //$this->error(implode(',', $result['data']['no']));
                    }
                }
                break;
        }

        try {
            \addons\uniprint\library\printer::printingTemplate($template, $printer->id);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }

        $this->success('打印成功');
    }

    /**
     * 同步状态
     */
    public function sync()
    {
        $printers = $this->model->select();
        /**
         * @var \app\admin\model\uniprint\Printer $printer
         */
        foreach ($printers as $printer) {
            try {
                switch ($printer->brand_id) {
                    case 'zhongwu':
                        $rpc = new \zhongwu\protocol\RpcClient($this->configs['zhongwu']['appid'], $this->configs['zhongwu']['appsecret'], 'http://api.zhongwuyun.com');
                        $Zprinter = new \zhongwu\Printer($rpc);
                        $result = $Zprinter->set_args($printer->device_id, $printer->device_secret)->get_status();
                        if (isset($result->retData->status)) {
                            $printer->status = $result->retData->status;
                            $printer->save();
                        }
                        break;
                    case 'yilianyun':
                        $config = new \App\Config\YlyConfig($this->configs['yilianyun']['appid'], $this->configs['yilianyun']['appsecret']);

                        // 获取token token永不过期
                        $uniprint = get_addon_config('uniprint');
                        if (empty($uniprint['yilianyun']['access_token'])) {
                            $client = new \App\Oauth\YlyOauthClient($config);
                            $token = $client->getToken();   //若是开放型应用请传授权码code
                            $uniprint['yilianyun']['access_token'] = $token->access_token;
                            set_addon_config('uniprint', $uniprint);
                        }

                        // 绑定设备
                        $print = new \App\Api\PrinterService($uniprint['yilianyun']['access_token'], $config);
                        $res = $print->addPrinter($printer->device_id, $printer->device_secret, $printer->name, $printer->card);

                        $client = new \App\Protocol\YlyRpcClient($uniprint['yilianyun']['access_token'], $config);
                        $result = $client->call('printer/getprintstatus', array('machine_code' => $printer->device_id));
                        if (isset($result->body->state)) {
                            $printer->status = $result->body->state;
                            $printer->save();
                        }
                        break;
                    case 'feie':
                        $time = time();         //请求时间
                        $msgInfo = array(
                            'user' => $this->configs['feie']['USER'],
                            'stime' => $time,
                            'sig' => sha1($this->configs['feie']['USER'] . $this->configs['feie']['UKEY'] . $time),
                            'apiname' => 'Open_queryPrinterStatus',
                            'sn' => $printer->device_id
                        );
                        $client = new HttpClient($this->configs['feie']['IP'], $this->configs['feie']['PORT']);
                        if (!$client->post($this->configs['feie']['PATH'], $msgInfo)) {
                            throw new Exception('error');
                        } else {
                            $result = $client->getContent();
                            $result = json_decode($result, true);
                            switch ($result['data']) {
                                case '离线。':
                                    $printer->status = 0;
                                    break;
                                case '在线，工作状态正常。':
                                    $printer->status = 1;
                                    break;
                                case '在线，工作状态不正常。':
                                    $printer->status = 2;
                                    break;
                                default:
                                    $printer->status = 0;
                                    break;
                            }
                            $printer->save();
                        }
                        break;
                }
            } catch (\Exception $e) {
                // 不管什么错误都是显示离线
                $printer->status = 0;
                $printer->save();
                // $this->error($e->getMessage());
            }
        }
        $this->success('同步成功');
    }
}
