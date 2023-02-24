<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use app\common\model\SmsCode;
use app\common\model\SmsMobile;
use app\common\model\SmsUrl;

class GetCodeLrj extends Command
{
    public $aesKey = 'EA6BE594D61BFA53';
    public $tokenKey = 'KgrRk5JzHS';
    
    protected function configure()
    {
        // 指令配置
        $this->setName('getCodeLrj')
            ->setDescription('the getCodeLrj command');
    }

    protected function execute(Input $input, Output $output)
    {
        $onOff = SmsUrl::where(['id'=>85])->value('on_off');
        if($onOff == 2) return '老人机任务已关闭';
        $map[] = ['is_get', '=', 2];//1未取2已取
        $map[] = ['status', '<>', 1];
        $map[] = ['set_num', '<=', 10];
        $map[] = ['send_check', '=', 4];//检测开通抖音状态：1待提交2已提交3已开通抖音4未开通抖音
        $map[] = ['sms_url_id', '=', 85];
        $mobile_list = SmsMobile::where($map)->select()->toArray();
        // echo SmsMobile::getlastsql();die;
        if(empty($mobile_list)){
            echo 'mobile_list is empty';die;
        }
        foreach ($mobile_list as $v){
            $phone = $v['mobile'];
            $url = 'http://154.80.229.93/api/unify/codeSms?mobile='.$phone.'&token='.$this->tokenKey;
            $res = $str =  http_curl($url);
            $res = explode('|', $res);
            if($res[0] != 1){
                $url = 'http://154.80.229.93/api/unify/codeSms?mobile=86'.$phone.'&token='.$this->tokenKey;
                $res = $str =  http_curl($url);
                $res = explode('|', $res);
                if($res[0] != 1){
                    echo 'Error: phone:'.$phone.';return:'.$str."\n";
                    continue;
                }
            }
            $sta = SmsCode::where('mobile',$phone)->find();
            if(!empty($sta)){
                SmsCode::where('mobile', $phone)->update([
                    'code' => $res[1],
                    'create_time' => time()
                ]);
            }else{
                SmsCode::insert([
                    'mobile' => $phone,
                    'code' => $res[1],
                    'create_time' => time()
                ]);
            }
            echo 'Success: phone:'.$phone.';return:'.$str."\n";
        }
        // 指令输出
        $output->writeln('success');
    }
}
