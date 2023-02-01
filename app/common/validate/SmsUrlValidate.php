<?php
/**
 * 渠道链接验证器
 */

namespace app\common\validate;

class SmsUrlValidate extends CommonBaseValidate
{
    protected $rule = [
            'channel_name|渠道名称' => 'require',
    'channel|渠道编码' => 'require',
    'send_url|发码URL' => 'require',
    'receive_url|取码URL' => 'require',
    ];

    protected $message = [
            'channel_name.required' => '渠道名称不能为空',
    'channel.required' => '渠道编码不能为空',
    'send_url.required' => '发码URL不能为空',
    'receive_url.required' => '取码URL不能为空',
    ];

    protected $scene = [
        'admin_add'     => ['channel_name', 'channel', 'send_url', 'receive_url', ],
        'admin_edit'    => ['id', 'channel_name', 'channel', 'send_url', 'receive_url', ],
        'admin_del'     => ['id', ],
        'admin_disable' => ['id', ],
        'admin_enable'  => ['id', ],
        'api_add'       => ['channel_name', 'channel', 'send_url', 'receive_url', ],
        'api_info'      => ['id', ],
        'api_edit'      => ['id', 'channel_name', 'channel', 'send_url', 'receive_url', ],
        'api_del'       => ['id', ],
        'api_disable'   => ['id', ],
        'api_enable'    => ['id', ],
    ];
}
