<?php
/**
 * 收码模型
*/

namespace app\common\model;



class SmsCode extends CommonBaseModel
{
    
    // 自定义选择数据
    

    protected $name = 'sms_code';
    protected $autoWriteTimestamp = true;

    // 可搜索字段
    public array $searchField = ['mobile',];

    // 可作为条件的字段
    public array $whereField = [];

    // 可作为多选条件的字段
    public array $multiWhereField = [];

    // 可做为时间
    public array $timeField = [];

    

    

}
