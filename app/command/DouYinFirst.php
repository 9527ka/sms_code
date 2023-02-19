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

class DouYinFirst extends Command
{
    //新版码商 V3
    public $http = 'http://118.195.190.73:8061';
    public $tokenStr = '?token=6cf237dc9911f3b757c0f54207c71169&projectID=dyzc888';//token
    
    protected function configure()
    {
        // 指令配置
        $this->setName('douYinFirst')
            ->setDescription('the douYinFirst command');
    }

    protected function execute(Input $input, Output $output)
    {
        echo "抖音首次：\n";
        $onOff = SmsUrl::where('id',127)->value('on_off');
        if($onOff == 2){
            return "任务已关闭\n";
        }
        $msg = $this->_getPhone();//取号
        echo $msg."\n";
        
        $map[] = ['sms_url_id','=',127];
        $map[] = ['status','<>',0];
        $map[] = ['status','<>',1];
        $map[] = ['set_num','<',10];
        $phone_list = SmsMobile::where($map)->field('mobile')->select();
        if(empty($phone_list)){
            return "暂无可取号码\n";
        }
        foreach ($phone_list as $v){
            $msg = $this->_getCode($v['mobile']);//取码
            echo $msg."\n";
        }
    }
    
    private function _getPhone(){
        echo "取号：\n";
        $url = $this->http.'/api/getPhone'.$this->tokenStr;
        $res = http_curl($url);
        // $res = '{"code":"10000","data":{"currentAuthority":"user","phone":"18983250113","status":"ok"}}';
        $res = json_decode($res, true);
        // var_dump($res);die;
        if($res['code'] != 10000){
            return $res['code'].'|'.$res['msg']."\n";
        }
        $phone = $res['data']['phone'];
        
        $sta = SmsMobile::where(['mobile' => $phone])->find();
        if(!$sta){
            SmsMobile::insert([
                'sms_url_id' => '127',
                'mobile' => $phone,
                'create_time' => time()
            ]);
        }
        return "get phone success: ".$phone."\n";
    }
    private function _getCode($phone){
        echo "$phone 取码：\n";
        $url = $this->http.'/api/getPhoneSms'.$this->tokenStr.'&phone='.$phone;
        $res = http_curl($url);
        $res = json_decode($res, true);
        // var_dump($res);die;
        if($res['code'] != 10000){
            return $res['msg'];
        }
        $code = $res['data']['sms'];
        $sta = SmsCode::where(['mobile' => $phone])->find();
        if(!$sta){
            SmsCode::insert([
                'mobile' => $phone,
                'code' => $code,
                'create_time' => time()
            ]);
        }else{
            SmsCode::where('mobile', $phone)->update([
                'code' => $code,
                'create_time' => time()
            ]);
        }
        return "get Code success: ".$phone."|".$code."\n";
    }
}
