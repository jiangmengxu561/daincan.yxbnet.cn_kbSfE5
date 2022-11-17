<?php

namespace app\admin\model\uniprint;

use think\Model;

/**
 * Class Printer
 * @package app\admin\model\uniprint
 * @property int $id
 * @property string $brand_id
 * @property string $device_id
 * @property string $device_secret
 * @property string $status
 * @property string $name
 * @property string $card
 * @property integer $number
 */
class Printer extends Model
{

    // 表名
    protected $name = 'uniprint_printer';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'brand'
    ];

    /**
     * 品牌信息
     */
    public function getBrandAttr($value, $data)
    {
        $configs = get_addon_fullconfig('uniprint');
        foreach ($configs as $config) {
            if ($config['name'] == $data['brand_id']) {
                return $config;
            }
        }
        return [];
    }







}
