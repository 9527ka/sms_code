<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use app\common\model\SmsMobile;
use app\common\model\SmsUrl;

//老人机先查询，再批量提交检测是否开通过抖音
class GetMobileLrjPullPhone extends Command
{
    public $aesKey = 'EA6BE594D61BFA53';
    public $tokenKey = 'KgrRk5JzHS';
    
    protected function configure()
    {
        // 指令配置
        $this->setName('getMobileLrjPullPhone')
            ->setDescription('the getMobileLrjPullPhone command');
    }

    protected function execute(Input $input, Output $output)
    {
        $onOff = SmsUrl::where(['id'=>85])->value('on_off');
        if($onOff == 2) return '老人机任务已关闭';
        $url = 'http://154.80.229.93/api/unify/getTelephone?token='.$this->tokenKey;
        $res = http_curl($url);
        $res = explode('|', $res);
        // var_dump($res);die;
        if($res[0] != 1){
            echo "1001,$res[1]\n";
            return false;
        }
        $phone = trim($res[1]);
        echo "get phone:".$phone."\n";
        //去除86前缀
        $str = substr($phone,0,2);
        if($str == '86'){
            $phone = substr($phone,2);
        }
        $sta = SmsMobile::insert([
            'sms_url_id' => 85,
            'mobile' => $phone,
            'send_check' => 4,//待提交
            'create_time' => time()
        ]);
        // 指令输出
        $output->writeln('号码：'.$phone.'获取成功，sta：'.$sta);
    }
    
}
