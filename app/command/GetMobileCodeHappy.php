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

//happy码商，获取手机号和验证码
class GetMobileCodeHappy extends Command
{
    public $aesKey = 'EA6BE594D61BFA53';
    //新版码商 V4
    public $http = 'http://8.134.144.219:33';
    public $tokenStr = '?username=asen01&password=123456&projectid=1020';//token
    
    protected function configure()
    {
        // 指令配置
        $this->setName('getMobileCodeHappy')
            ->setDescription('the getMobileCodeHappy command');
    }

    protected function execute(Input $input, Output $output)
    {
        $onOff = SmsUrl::where(['id'=>128])->value('on_off');
        if($onOff == 2) return 'happy码商任务已关闭';
        //从接口获取号码
        $this->_getApiPhone();
        
        //从接口获取验证码
        $this->_getApiCode();
    }
    //从接口获取号码
    private function _getApiPhone(){
        $url = $this->http.'/api/Product/GetProduct'.$this->tokenStr;
        echo "Happy，获取号码开始\n";
        $res = http_curl($url);
        $res = json_decode($res);
        if($res->code != 0){
            echo date("Y-m-d H:i:s", time()).$res->message."\n";die;
        }
        $phone = $res->data;
        SmsMobile::insert([
            'sms_url_id' => '128',
            'mobile' => $phone,
            'create_time' => time()
        ]);
        echo date("Y-m-d H:i:s", time()).$phone." get \n";
    }
    //从接口获取验证码
    private function _getApiCode(){
        echo "Happy，获取验证码开始\n";
        $map[] = ['is_get', '=', 2];//1未取2已取
        $map[] = ['status', '<>', 1];
        $map[] = ['set_num', '<=', 10];
        $map[] = ['send_check', '=', 4];//检测开通抖音状态：1待提交2已提交3已开通抖音4未开通抖音
        $map[] = ['sms_url_id', '=', 128];
        $mobile_list = SmsMobile::where($map)->field('mobile')->select()->toArray();
        // echo SmsMobile::getlastsql();die;
        if(empty($mobile_list)){
            echo date("Y-m-d H:i:s", time()).'happy mobile_list is empty';die;
        }
        foreach ($mobile_list as $v){
            $phone = $v['mobile'];
            $url = $this->http.'/api/Product/GetProductCode'.$this->tokenStr."&productcode=".$phone;
            $res = http_curl($url);
            $res = json_decode($res);
            if($res->code != 0){
                return ['code' => 101, 'msg' => $res->message];
            }
            $code = $res->data;
            echo $phone." get code is ".$code."\n";
            $sta = SmsCode::where('mobile',$phone)->find();
            if(!empty($sta)){
                SmsCode::where('mobile', $phone)->update([
                    'code' => $code,
                    'create_time' => time()
                ]);
            }else{
                SmsCode::insert([
                    'mobile' => $phone,
                    'code' => $code,
                    'create_time' => time()
                ]);
            }
        }
    }
}
