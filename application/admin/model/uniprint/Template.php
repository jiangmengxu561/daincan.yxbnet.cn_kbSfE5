<?php

namespace app\admin\model\uniprint;

use think\Model;

/**
 * 模板
 * Class Template
 * @package app\admin\model\uniprint
 * @property string $template
 */
class Template extends Model
{

    // 表名
    protected $name = 'uniprint_template';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'brand'
    ];


    public function getTypeList()
    {
        return ['外卖' => __('外卖'), '商超' => __('商超'), '物流' => __('物流')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


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
