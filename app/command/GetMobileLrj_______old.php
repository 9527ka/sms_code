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
use app\common\model\SmsCode;
use app\common\model\SmsDouyin;

//老人机查询一个号并提交检测是否开通过抖音---10秒左右一个
class GetMobileLrj extends Command
{
    public $aesKey = 'EA6BE594D61BFA53';
    public $tokenKey = 'KgrRk5JzHS';
    
    protected function configure()
    {
        // 指令配置
        $this->setName('getMobileLrj')
            ->setDescription('the getMobileLrj command');
    }

    protected function execute(Input $input, Output $output)
    {
        $url = 'http://154.80.229.93/api/unify/getTelephone?token='.$this->tokenKey;
        $res = http_curl($url);
        $res = explode('|', $res);
        // var_dump($res);die;
        if($res[0] != 1){
            echo "1001,.$res[1]\n";
            return false;
        }
        $phone = $res[1];
        // $phone = '13100263470';
        //提交检测
        $sta = $this->_sendCheckPhone($phone);
        if(!$sta){
            echo "提交检测失败\n";
        }
        sleep(6);
        //检测
        $sta = $this->_checkPhone($phone);
        if($sta){
            $output->writeln('号码：'.$phone.'已注册');
        }
        // 指令输出
        $output->writeln('号码：'.$phone.'获取成功');
    }
    
    //提交待检测的号码
    private function _sendCheckPhone($phone){
        $url = 'http://121.196.204.13/dy/api_upload_number.php';
        $d['pro_code'] = 'rand';
        $d['plat'] = 'douyin';//douyin|kuaishou
        $d['user_id'] = '200159';
        $d['user_token'] = '252115C9-6785-476b-8C46-9F5395A685B9';
        $d['country_code'] = '86';
        $d['contents'] = $phone;
        $res = http_curl($url, $d, true);
        if(!$res){
            echo "Error: 1002, check phone($phone) fail\n";
            return false;
        }
        $res = json_decode($res,true);
        if($res['code'] == "1"){
            //去除86前缀
            $str = substr($phone,0,2);
            if($str == '86'){
                $phone = substr($phone,2);
            }
            SmsMobile::insert([
                'is_get' => 1,
                'sms_url_id' => 85,
                'mobile' => $phone,
                'create_time' => time()
            ]);
            echo "Success: 1002, check phone($phone) success\n";
            return true;
        }else{
            echo "Error: 1002, check phone($phone) fail\n";
            return false;
        }
    }
    //检测手机号是否注册抖音|快手
    private function _checkPhone($phone){
        $sta = SmsDouyin::where(['mobile_number' => $phone])->find();
        if($sta){
            echo "Error: phone($phone) is register\n";
            return true;//已注册
        }else{
            $url = 'http://121.196.204.13/dy/api_get_number_result.php';
            $d['plat'] = 'douyin';//douyin|kuaishou
            $d['user_id'] = '200159';
            $d['user_token'] = '252115C9-6785-476b-8C46-9F5395A685B9';
            $d['country_code'] = '86';
            $d['contents'] = $phone;
            
            $res = http_curl($url, $d, true);
            if(!$res){
                return "Error: 1003, check request fail\n";
            }
            $res = json_decode($res,true);
            // var_dump($res);die;
            if($res['code'] == "1"){
                if(empty($res['data'])){
                    echo "Success: get phone($phone) success\n";
                    return false;//未注册
                }
                SmsDouyin::insert([
                    'country_code' => $res['data'][0]['country_code'],
                    'mobile_number' => $res['data'][0]['mobile_number'],
                    'mobile_area' => $res['data'][0]['mobile_area'],
                    'result_text' => $res['data'][0]['result_text'],
                    'user_info' => $res['data'][0]['user_info']
                ]);
                // echo SmsDouyin::getlastsql();die;
                if($res['data'][0]['result_text'] == '首发号'){
                    echo "Error: phone($phone) is register\n";
                    return false;//未注册
                }
                return true;//已注册
            }
        }
    }
}
