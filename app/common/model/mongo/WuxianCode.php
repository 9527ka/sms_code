<?php
/**
 * +----------------------------------------------------------------------
 * | 无限码模型
 * +----------------------------------------------------------------------
 */
namespace app\common\model\mongo;
use think\Model;
class WuxianCode extends Model
{
	/** 设置数据库配置 */
	protected $connection = 'mongo';
	/** 自动完成 */
	protected $auto = [];
    protected $insert = [
        'mobile' => '',
        'code' => '',
        'create_time' => '',
        'update_time' => '',
    ];
    protected $update = [];
    /** 设置json类型字段 */
    protected $json = [
    ];
    /** 类型转换 */
    protected $type = [
        'content_type' => 'integer',
        'msg_type' => 'integer',
        'time' => 'integer',
    ];
    protected static function init()
    {
    }
}
