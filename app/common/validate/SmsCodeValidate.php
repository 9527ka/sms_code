<?php
/**
 * 收码验证器
 */

namespace app\common\validate;

class SmsCodeValidate extends CommonBaseValidate
{
    protected $rule = [
            'mobile|手机号' => 'require',
    'code|验证码' => 'require',

    ];

    protected $message = [
            'mobile.required' => '手机号不能为空',
    'code.required' => '验证码不能为空',

    ];

    protected $scene = [
        'admin_add'     => ['mobile', 'code', ],
        'admin_edit'    => ['id', 'mobile', 'code', ],
        'admin_del'     => ['id', ],
        'admin_disable' => ['id', ],
        'admin_enable'  => ['id', ],
        'api_add'       => ['mobile', 'code', ],
        'api_info'      => ['id', ],
        'api_edit'      => ['id', 'mobile', 'code', ],
        'api_del'       => ['id', ],
        'api_disable'   => ['id', ],
        'api_enable'    => ['id', ],
    ];
}
