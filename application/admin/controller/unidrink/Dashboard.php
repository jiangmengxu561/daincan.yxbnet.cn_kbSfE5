<?php

namespace app\admin\controller\unidrink;

use app\admin\model\Admin;
use app\admin\model\unidrink\Goods;
use app\admin\model\unidrink\Income;
use app\admin\model\User;
use app\common\controller\Backend;
use app\common\model\Attachment;
use fast\Date;
use think\Db;

/**
 * 控制台
 *
 * @icon   fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 查看
     */
    public function index()
    {
        if ($this->auth->isSuperAdmin()) {
            $this->view->assign([
                'totalCategory' => \app\admin\model\unidrink\Category::count(),
                'storeNums' => \addons\unidrink\model\Shop::count(),
                'goodsNums' => \app\admin\model\unidrink\Product::count(),
                'orderNums' => (new \app\admin\model\unidrink\Order)->where('status = 1 and have_paid != 0')->count(),
                'totalCoupon' => \app\admin\model\unidrink\Coupon::count(),
                'totalAds' => \app\admin\model\unidrink\Ads::count(),
            ]);
        }
        $shopList = (new \addons\unidrink\model\Shop())->getShopIdFromAdminId($this->auth->id);
        $this->assignconfig('shopList', $shopList);
        return $this->view->fetch();
    }

    /**
     * 收入明细
     */
    public function income()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);

        try {
            \think\Db::execute("SET @@sql_mode='';");
        } catch (\Exception $e) {

        }
        $shopList = (new \addons\unidrink\model\Shop())->getShopIdFromAdminId($this->auth->id);
        $shopIds = array_keys($shopList);

        $model = new Income();
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $total = $model
            ->where($where)
            ->where('shop_id','in', $shopIds)
            ->field('DATE_FORMAT(FROM_UNIXTIME(date_time), "%Y-%m-%d") AS join_date')
            ->group('join_date')
            ->count();

        $list = $model
            ->where($where)
            ->where('shop_id','in', $shopIds)
            ->field('date_time,shop_id,shop_name,SUM(order_amount) as order_amount,COUNT(*) as order_nums,DATE_FORMAT(FROM_UNIXTIME(date_time), "%Y-%m-%d") AS join_date')
            ->group('join_date')
            ->order($sort, $order)
            ->limit($offset, $limit)
            ->select();

        $result = array(
            "total" => $total,
            "rows" => $list,
            'join_date' => array_column($list, 'join_date'),
            'order_amount' => array_column($list, 'order_amount'),
            'order_nums' => array_column($list, 'order_nums'),
        );

        return json($result);
    }


    /**
     * 商品销量
     */
    public function goods()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);

        try {
            \think\Db::execute("SET @@sql_mode='';");
        } catch (\Exception $e) {

        }
        $shopList = (new \addons\unidrink\model\Shop())->getShopIdFromAdminId($this->auth->id);
        $shopIds = array_keys($shopList);

        $model = new Goods();
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $total = $model
            ->where($where)
            ->where('shop_id','in', $shopIds)
            ->field('DATE_FORMAT(FROM_UNIXTIME(date_time), "%Y-%m-%d") AS join_date')
            ->group('join_date')
            ->fetchSql(true)
            ->count();

        $list = $model
            ->where($where)
            ->where('shop_id','in', $shopIds)
            ->field('date_time,shop_id,SUM(number) as sell_total,product_name,DATE_FORMAT(FROM_UNIXTIME(date_time), "%Y-%m-%d") AS join_date')
            ->group('join_date,product_name')
            ->order($sort, $order)
            ->order('sell_total', 'desc')
            ->limit($offset, $limit)
            ->select();

        $result = array(
            "total" => $total,
            "rows" => $list
        );

        return json($result);
    }
}
