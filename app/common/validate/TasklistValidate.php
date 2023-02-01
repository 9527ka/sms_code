<?php
/**
 * 发码列表验证器
 */

namespace app\common\validate;

class TasklistValidate extends CommonBaseValidate
{
    protected $rule = [
            'com|com' => 'require',
    'content|内容' => 'require',
    'mobile|手机号' => 'require',
    'msg|状态' => 'require',

    ];

    protected $message = [
            'com.required' => 'com不能为空',
    'content.required' => '内容不能为空',
    'mobile.required' => '手机号不能为空',
    'msg.required' => '状态不能为空',

    ];

    protected $scene = [
        'admin_add'     => ['com', 'content', 'mobile', 'msg', ],
        'admin_edit'    => ['id', 'com', 'content', 'mobile', 'msg', ],
        'admin_del'     => ['id', ],
        'admin_disable' => ['id', ],
        'admin_enable'  => ['id', ],
        'api_add'       => ['com', 'content', 'mobile', 'msg', ],
        'api_info'      => ['id', ],
        'api_edit'      => ['id', 'com', 'content', 'mobile', 'msg', ],
        'api_del'       => ['id', ],
        'api_disable'   => ['id', ],
        'api_enable'    => ['id', ],
    ];
}
