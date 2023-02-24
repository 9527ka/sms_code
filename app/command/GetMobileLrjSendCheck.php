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

//老人机先查询，再批量提交检测是否开通过抖音
class GetMobileLrjSendCheck extends Command
{
    public $aesKey = 'EA6BE594D61BFA53';
    public $tokenKey = 'KgrRk5JzHS';
    
    protected function configure()
    {
        // 指令配置
        $this->setName('getMobileLrjSendCheck')
            ->setDescription('the getMobileLrjSendCheck command');
    }

    protected function execute(Input $input, Output $output)
    {
        $onOff = SmsUrl::where(['id'=>85])->value('on_off');
        if($onOff == 2) return '老人机任务已关闭';
        
        $this->_sendCheckPhone();
        $output->writeln('send check phone finished!');
        $output->writeln('sleep 6s, wait pull check!');
        sleep(6);
        $this->_checkPhone();
        $output->writeln('pull check phone finished!');
    }
    
    //提交待检测的号码
    private function _sendCheckPhone(){
        $mobile = SmsMobile::where(['sms_url_id' => 85,'send_check' => 1])->field('GROUP_CONCAT(mobile) as mobile_str')->find();
        $url = 'http://121.196.204.13/dy/api_upload_number.php';
        $d['pro_code'] = 'rand';
        $d['plat'] = 'douyin';//douyin|kuaishou
        $d['user_id'] = '200159';
        $d['user_token'] = '252115C9-6785-476b-8C46-9F5395A685B9';
        $d['country_code'] = '86';
        $d['contents'] = $mobile->mobile_str;
        $res = http_curl($url, $d, true);
        if(!$res){
            echo "Error: 1002, check phone($phone) fail\n";
            return false;
        }
        $res = json_decode($res,true);
        if($res['code'] == "1"){
            SmsMobile::where('mobile','in',$mobile->mobile_str)->update(['send_check' => 2]);
            echo "Success: 1002, send check phone(".$mobile->mobile_str.") success\n";
            return true;
        }else{
            echo "Error: 1002, send check phone(".$mobile->mobile_str.") fail\n";
            return false;
        }
    }
    //手机号是否注册抖音|快手--获取检测结果
    //检测成功后，改为已检测 send_check = 1
    private function _checkPhone(){
        $mobile = SmsMobile::where(['sms_url_id' => 85,'send_check' => 2])->field('GROUP_CONCAT(mobile) as mobile_str')->find();
        if(!$mobile){
            echo "Error: wait send check phone is NULL\n";//待检测的手机为空
            return true;//已注册
        }
        $url = 'http://121.196.204.13/dy/api_get_number_result.php';
        $d['plat'] = 'douyin';//douyin|kuaishou
        $d['user_id'] = '200159';
        $d['user_token'] = '252115C9-6785-476b-8C46-9F5395A685B9';
        $d['country_code'] = '86';
        $d['contents'] = $mobile->mobile_str;
        
        $res = http_curl($url, $d, true);
        // var_dump($res);die;
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
            foreach ($res['data'] as $v){
                //录入已抖音信息
                // SmsDouyin::where('mobile_number',$v['mobile_number'])update([
                //     'country_code' => $v['country_code'],
                //     'mobile_number' => $v['mobile_number'],
                //     'mobile_area' => $v['mobile_area'],
                //     'result_text' => $v['result_text'],
                //     'user_info' => $v['user_info']
                // ]);
                $send_check = 3;//已开通抖音
                if($v['result_text'] == '首发号' || $v['result_text'] == '活号'){
                    $send_check = 4;//未开通抖音
                }
                //更新检测后的状态
                SmsMobile::where(['mobile' => $v['mobile_number'],'sms_url_id'=>85])->update([
                    'send_check' => $send_check,
                    'system_remark' => $v['result_text']
                ]);
            }
            return true;//已注册
        }
    }
}
