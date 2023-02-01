<?php
/**
 * 收码控制器
 */
namespace app\api\controller;

use think\response\Json;
use app\api\service\smsNewService;
use app\common\validate\smsNewValidate;
use app\api\exception\ApiServiceException;
use app\common\model\smsNew;
use app\common\model\SmsMobile;
use app\common\model\SmsUrl;
use app\common\model\SmsCode;
use app\common\model\SmsDouyin;
use think\facade\Db;
use think\facade\Cache;

class SmsV1Controller extends ApiBaseController
{
    protected array $loginExcept= [
        'api/smsV1/checkPhone',
        'api/smsV1/getPhone',
        'api/smsV1/send',
        'api/smsV1/getCode',
        'api/smsV1/feedBack',
        'api/smsV1/sendCheckPhone',
        'api/smsV1/feedBackTest',
    ];
    
    public $aesKey = 'EA6BE594D61BFA53';
    public $tokenKey = 'KgrRk5JzHS';
    
    # 获取验证码,取码接口
    public function getCode(){
        $p = $this->param;
        if(empty($p['phone'])){
            return api_error('手机号不能为空');
        }
        $phone = '15396492529';
        $url = 'http://154.80.229.93/api/unify/codeSms?mobile='.$phone.'&token='.$this->tokenKey;
        $res = http_curl($url);
        $res = explode('|', $res);
        if($res[0] != 1){
            return api_error($res[1]);
        }
        
        SmsCode::insert([
            'mobile' => $phone,
            'code' => $res[1],
            'create_time' => time()
        ]);
        
        return api_success(['verifyCode'=>$res[1]],'Success');
    }
    # 获取手机号取卡接口
    public function getPhone(){
        $url = 'http://154.80.229.93/api/unify/getTelephone?token='.$this->tokenKey;
        $res = http_curl($url);
        $res = explode('|', $res);
        // var_dump($res);die;
        if($res[0] != 1){
            return api_error('1001,'.$res[1]);
        }
        $phone = $res[1];
        // $phone = '13100263470';
        //提交检测
        $sta = $this->_sendCheckPhone($phone);
        if(!$sta){
            return api_error('提交检测失败');
        }
        sleep(6);
        //检测
        $sta = $this->_checkPhone($phone);
        if($sta){
            return api_error('号码：'.$phone.'已注册');
        }
        // echo SmsMobile::getlastsql();die;
        // return api_success(['phone'=>$res['data']['result']],'Success');
        $mobile = enAES($res['data']['result'], $this->aesKey);
        return api_success(['phone'=>$mobile],'Success');
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
            return api_error('Error');
        }
        $res = json_decode($res,true);
        if($res['code'] == "1"){
            SmsMobile::insert([
                'is_get' => 1,
                'sms_url_id' => 85,
                'mobile' => $phone,
                'create_time' => time()
            ]);
            return true;
            // if($res['coin']['renmain'] < 100){
            // }
        }else{
            return false;
        }
    }
    //检测手机号是否注册抖音|快手
    // public function checkPhone(){
    private function _checkPhone($phone){
        // $phone = '13199562361';
        $sta = SmsDouyin::where(['mobile_number' => $phone])->find();
        if($sta){
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
                return api_error('Error');
            }
            $res = json_decode($res,true);
            // var_dump($res);die;
            if($res['code'] == "1"){
                if(empty($res['data'])){
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
                    return false;//未注册
                }
                return true;//已注册
            }
        }
    }
    
    public function sendCheckPhone($phone){
        $url = 'http://121.196.204.13/dy/api_upload_number.php';
        $d['pro_code'] = 'rand';
        $d['plat'] = 'douyin';//douyin|kuaishou
        $d['user_id'] = '200159';
        $d['user_token'] = '252115C9-6785-476b-8C46-9F5395A685B9';
        $d['country_code'] = '86';
        $d['contents'] = $phone;
        $res = http_curl($url, $d, true);
        if(!$res){
            return api_error('Error');
        }
        $res = json_decode($res,true);
        if($res['code'] == "1"){
            SmsMobile::insert([
                'is_get' => 2,
                'sms_url_id' => 85,
                'mobile' => $phone,
                'create_time' => time()
            ]);
            return true;
        }else{
            return false;
        }
    }
    # 反馈接口
    public function feedBack(){
        $p = $this->param;
        if(!$p['phone']){
            return api_error('手机号不能为空');
        }
        if(!isset($p['status'])){
            return api_error('状态不能为空');
        }
        if(!in_array($p['status'],[0,1,2])){
            return api_error('状态错误');
        }
        $phone = trim(deAES($p['phone'], $this->aesKey));
        // $phone = $p['phone'];
        //针对已取到的手机号做备注、状态变更
        $mobileInfo = SmsMobile::where(['is_get' => 2, 'mobile' => $phone])->find();
        if(empty($mobileInfo)){
            return api_error('手机号不存在或未取卡');
        }
        
        $getStatus = $p['status'] == 2 ? 1 : 2;//注册失败状态时，已取状态重置
        SmsMobile::where(['id' => $mobileInfo->id])->update([
            'is_get' => $getStatus,//重置状态
            'status' => $p['status'],
            'remark' => $p['remark'],
            'update_time' => time()
        ]);
        SmsMobile::where(['id' => $mobileInfo->id])->inc('set_num',1)->update();
        return api_success([],'Success');
    }
    
}
