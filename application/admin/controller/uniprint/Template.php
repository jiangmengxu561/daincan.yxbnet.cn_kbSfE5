<?php

namespace app\admin\controller\uniprint;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 中午打印机模板
 *
 * @icon fa fa-circle-o
 */
class Template extends Backend
{

    /**
     * Feie模型对象
     * @var \app\admin\model\uniprint\Template $model
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\uniprint\Template();
        $this->view->assign("typeList", $this->model->getTypeList());

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
}
