<?php
/**
 * 手机号验证器
 */

namespace app\common\validate;

class SmsMobileValidate extends CommonBaseValidate
{
    protected $rule = [
            'com|com' => 'require',
    'mobile|手机号' => 'require',
    'remark|备注' => 'require',
    ];

    protected $message = [
            'com.required' => 'com不能为空',
    'mobile.required' => '手机号不能为空',
    'remark.required' => '备注不能为空',
    ];

    protected $scene = [
        'admin_add'     => ['sms_url_id', 'com', 'mobile', 'is_get', 'status', 'code', 'remark', ],
        'admin_edit'    => ['id', 'sms_url_id', 'com', 'mobile', 'is_get', 'status', 'code', 'remark', ],
        'admin_del'     => ['id', ],
        'admin_disable' => ['id', ],
        'admin_enable'  => ['id', ],
        'api_add'       => ['sms_url_id', 'com', 'mobile', 'is_get', 'status', 'code', 'remark', ],
        'api_info'      => ['id', ],
        'api_edit'      => ['id', 'sms_url_id', 'com', 'mobile', 'is_get', 'status', 'code', 'remark', ],
        'api_del'       => ['id', ],
        'api_disable'   => ['id', ],
        'api_enable'    => ['id', ],
    ];
}
