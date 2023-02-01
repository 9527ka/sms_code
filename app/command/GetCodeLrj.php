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
        $map[] = ['is_get', '=', 2];//1未取2已取
        $map[] = ['status', '<>', 1];
        $map[] = ['set_num', '<=', 3];
        $mobile_list = SmsMobile::where($map)->select()->toArray();
        if(empty($mobile_list)){
            return 'mobile_list is empty';
        }
        foreach ($mobile_list as $v){
            $phone = $v['mobile'];
            $url = 'http://154.80.229.93/api/unify/codeSms?mobile='.$phone.'&token='.$this->tokenKey;
            $res = $str =  http_curl($url);
            $res = explode('|', $res);
            if($res[0] != 1){
                echo 'Error: phone:'.$phone.';return:'.$str."\n";
                continue;
            }
            $sta = SmsCode::where('mobile',$phone)->find();
            if(!empty($sta)){
                continue;
            }
            echo 'Success: phone:'.$phone.';return:'.$str."\n";
            
            SmsCode::insert([
                'mobile' => $phone,
                'code' => $res[1],
                'create_time' => time()
            ]);
        }
        // 指令输出
        $output->writeln('success');
    }
}
