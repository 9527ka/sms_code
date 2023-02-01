<?php
/**
 * 渠道链接模型
*/

namespace app\common\model;

// use think\model\concern\SoftDelete;

class SmsUrl extends CommonBaseModel
{
    // use SoftDelete;
    // 自定义选择数据
    

    protected $name = 'sms_url';
    protected $autoWriteTimestamp = true;

    // 可搜索字段
    public array $searchField = ['channel_name'];

    // 可作为条件的字段
    public array $whereField = [];

    // 可作为多选条件的字段
    public array $multiWhereField = [];

    // 可做为时间
    public array $timeField = [];

    

    /**
    * 关联收码
    */
    public function smsMobile()
    {
        return $this->hasMany(SmsMobile::class);
    }

}
