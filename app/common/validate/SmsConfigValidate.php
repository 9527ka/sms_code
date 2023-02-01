<?php
/**
 * 配置项验证器
 */

namespace app\common\validate;

class SmsConfigValidate extends CommonBaseValidate
{
    protected $rule = [
            'key|配置项' => 'require',
    'value|配置值' => 'require',
    'description|描述' => 'require',

    ];

    protected $message = [
            'key.required' => '配置项不能为空',
    'value.required' => '配置值不能为空',
    'description.required' => '描述不能为空',

    ];

    protected $scene = [
        'admin_add'     => ['key', 'value', 'description', ],
        'admin_edit'    => ['id', 'key', 'value', 'description', ],
        'admin_del'     => ['id', ],
        'admin_disable' => ['id', ],
        'admin_enable'  => ['id', ],
        'api_add'       => ['key', 'value', 'description', ],
        'api_info'      => ['id', ],
        'api_edit'      => ['id', 'key', 'value', 'description', ],
        'api_del'       => ['id', ],
        'api_disable'   => ['id', ],
        'api_enable'    => ['id', ],
    ];
}
