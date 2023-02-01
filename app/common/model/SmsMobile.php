<?php
/**
 * 手机号模型
*/

namespace app\common\model;



class SmsMobile extends CommonBaseModel
{
    
    // 自定义选择数据
    // 取号状态列表
const IS_GET_LIST= [
1=>'未取',
2=>'已取',
];
// 状态列表
const STATUS_LIST= [
-1=>'初始',
0=>'失败',
1=>'成功',
2=>'注册失败',
];
const BOOLEAN_TEXT = [0 => '否', 1 => '是'];

    protected $name = 'sms_mobile';
    protected $autoWriteTimestamp = true;

    // 可搜索字段
    public array $searchField = ['mobile',];

    // 可作为条件的字段
    public array $whereField = [];

    // 可作为多选条件的字段
    public array $multiWhereField = [];

    // 可做为时间
    public array $timeField = [];

    
    /**
    * 取号状态获取器
    */
    public function getIsGetNameAttr($value ,$data)
    {
        return self::IS_GET_LIST[$data['is_get']];
    }
/**
     * 取号状态获取器
    */
    public function getIsGetTextAttr($value, $data): string
    {
        return self::IS_GET_LIST[$data['is_get']];
    }

    /**
    * 状态获取器
    */
    public function getStatusNameAttr($value ,$data)
    {
        return self::STATUS_LIST[$data['status']];
    }
/**
     * 状态获取器
    */
    public function getStatusTextAttr($value, $data): string
    {
        return self::STATUS_LIST[$data['status']];
    }
}
