<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
// use app\common\model\mongo\WuxianCode as wxModel;
use app\common\model\SmsMobile;
use app\common\model\SmsCode;
use app\common\model\SmsUrl;

class WuxianCode extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('wuxianCode')
            ->setDescription('the wuxianCode command');
    }

    protected function execute(Input $input, Output $output)
    {
        $days_time = time()-43200;
        $map[] = ['sms_url_id','=',126];
        $map[] = ['status','<>',0];
        $map[] = ['status','<>',1];
        $map[] = ['set_num','<',20];
        // $map[] = ['create_time','>',$days_time];//创建时间大于12小时前
        $onOff = SmsUrl::where('id',126)->value('on_off');
        if($onOff == 2){
            exit("无限码任务已关闭\n");
        }
        $smsInfo = SmsMobile::where($map)->field('GROUP_CONCAT(mobile) as mobile_str')->find();
        if(empty($smsInfo->mobile_str)) return "----not phone----\n";
        $mobileArr = explode(',',$smsInfo->mobile_str);
        $mobileArrNew = [];
        echo "haved mobile:\n";
        foreach ($mobileArr as $v){
            $mobile = substr($v, 0, 3) . "****" . substr($v, 7, 11);
            $mobileArrNew[] = $mobile;
            echo $mobile."----\n";
        }
        
        $html = file_get_contents("http://47.94.157.44/Room.php?form=douyinrr");
        $list = explode("<tr>",$html);
        echo "\nget web mobile:\n";
        foreach ($list as $v){
            $str = str_replace("</td>","",$v);
            $str = str_replace("</tr>","",$str);
            $str = str_replace("\n","",$str);
            $tdArr = explode("<td>",$str);
            
            // if(!empty($tdArr[2])){
            //     echo trim($tdArr[2])."\n";
            // }
            // var_dump($mobileArrNew);
            
            //手机号,是否是自己需要的手机号
            if(!empty($tdArr[2]) && in_array(trim($tdArr[2]), $mobileArrNew)){
                $phone = trim($tdArr[2]);
                $code = explode("验证码", $tdArr[3])[1];
                $code = explode("，", $code)[0];
                if(empty($code)){
                    $code = explode("。", $code)[0];
                }
                $sta = SmsCode::where('mobile',$phone)->find();
                if(!$sta){
                    SmsCode::insert([
                        'mobile' => $phone,
                        'code' => $code,
                        'create_time' => time()
                    ]);
                    echo "\n $phone insert success----\n";
                    // echo SmsCode::getlastsql();
                }else{
                    SmsCode::where(['mobile' => $phone])->update([
                        'code' => $code,
                        'create_time' => time()
                    ]);
                    echo "\n $phone update success----\n";
                }
            }
        }
        // 指令输出
        // $output->writeln('wuxianCode success');
    }
}
