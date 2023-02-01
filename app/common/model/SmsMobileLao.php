<?php
/**
 * 老人机号码模型
*/

namespace app\common\model;



class SmsMobileLao extends CommonBaseModel
{
    
    // 自定义选择数据
    

    protected $name = 'sms_mobile_lao';
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
     * 是否提交检测，0待提交1已提交获取器
    */
    public function getIsSendCheckTextAttr($value, $data): string
    {
        return self::BOOLEAN_TEXT[$data['is_send_check']];
    }


    /**
    * 关联渠道链接
    */
    public function smsUrl()
    {
        return $this->belongsTo(SmsUrl::class);
    }

}
