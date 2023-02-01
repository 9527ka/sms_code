<?php
/**
 * 收码验证器
 */

namespace app\common\validate;

class SmscodeValidate extends CommonBaseValidate
{
    protected $rule = [
            'send_url|发码URL' => 'require',
    'receive_url|收码URL' => 'require',
    'mobile|手机号' => 'require',
    'code|验证码' => 'require',

    ];

    protected $message = [
            'send_url.required' => '发码URL不能为空',
    'receive_url.required' => '收码URL不能为空',
    'mobile.required' => '手机号不能为空',
    'code.required' => '验证码不能为空',

    ];

    protected $scene = [
        'admin_add'     => ['send_url', 'receive_url', 'mobile', 'status', 'code', ],
        'admin_edit'    => ['id', 'send_url', 'receive_url', 'mobile', 'status', 'code', ],
        'admin_del'     => ['id', ],
        'admin_disable' => ['id', ],
        'admin_enable'  => ['id', ],
        'api_add'       => ['send_url', 'receive_url', 'mobile', 'status', 'code', ],
        'api_info'      => ['id', ],
        'api_edit'      => ['id', 'send_url', 'receive_url', 'mobile', 'status', 'code', ],
        'api_del'       => ['id', ],
        'api_disable'   => ['id', ],
        'api_enable'    => ['id', ],
    ];
}
