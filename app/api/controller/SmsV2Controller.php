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
use think\facade\Db;
use think\facade\Cache;

class SmsV2Controller extends ApiBaseController
{
    protected array $loginExcept= [
        'api/smsV2/getToken',
        'api/smsV2/getPhone',
        'api/smsV2/send',
        'api/smsV2/getCode',
        'api/smsV2/feedBack',
        'api/smsV2/sendTest',
        'api/smsV2/feedBackTest',
    ];
    
    public $aesKey = 'EA6BE594D61BFA53';
    public $tokenKey = 'aa775411_token';
    
    private function _getToken(){
        //刷新token
        $token = Cache::get($this->tokenKey);
        $refreshUrl = 'http://47.95.0.77:9090/user/refresh?token='.$token;
        $refreshRes = http_curl($refreshUrl);
        $refreshRes = json_decode($refreshRes,true);
        //异常，重新登录
        if($refreshRes['code'] == 2001){
            $url = 'http://47.95.0.77:9090/user/doLogin?username=aa775411&password=112579';
            $res = http_curl($url);
            $res = json_decode($res,true);
            if($res['code'] == 2000){
                Cache::set($this->tokenKey,$res['data']['result']['token']);
                return $res['data']['result']['token'];
            }
        }else{
            Cache::set($this->tokenKey,$refreshRes['data']['result']['token']);
            return $refreshRes['data']['result']['token'];
        }
        return 'error';
    }
    
    # 获取验证码,取码接口
    public function getCode(){
        $p = $this->param;
        if(!$p['phone']){
            return api_error('手机号不能为空');
        }
        // $phone = trim(deAES($p['phone'], $this->aesKey));
        $phone = $p['phone'];
        $token = Cache::get($this->tokenKey);
        $url = 'http://47.95.0.77:9090/phone/query?phone='.$phone.'&token='.$token;
        $res = http_curl($url);
        $res = json_decode($res,true);
        //token过期时，刷新token
        if($res['code'] == 2001 && strstr($res['message'], '登录异常')){
            $token = $this->_getToken();
            $url = 'http://47.95.0.77:9090/phone/query?phone='.$phone.'&token='.$token;
            $res = http_curl($url);
            $res = json_decode($res,true);
        }
        if($res['code'] != 2000){
            return api_error($res['message']);
        }
        
        SmsCode::insert([
            'mobile' => $phone,
            'code' => $res['data']['result']['code'],
            'create_time' => time()
        ]);
        
        return api_success(['verifyCode'=>$res['data']['result']['code']],'Success');
    }
    # 获取手机号取卡接口
    public function getPhone(){
        $token = Cache::get($this->tokenKey);
        $url = 'http://47.95.0.77:9090/phone/require?phone=&token='.$token;
        $res = http_curl($url);
        $res = json_decode($res,true);
        //token过期时，刷新token
        if($res['code'] == 2001){
            $token = $this->_getToken();
            $url = 'http://47.95.0.77:9090/phone/require?phone=&token='.$token;
            $res = http_curl($url);
            $res = json_decode($res,true);
        }
        if($res['code'] != 2000){
            return api_error('获取失败');
        }
        SmsMobile::insert([
            'is_get' => 2,
            'sms_url_id' => 35,
            'mobile' => $res['data']['result'],
            'create_time' => time()
        ]);
        // echo SmsMobile::getlastsql();die;
        // return api_success(['phone'=>$res['data']['result']],'Success');
        $mobile = enAES($res['data']['result'], $this->aesKey);
        return api_success(['phone'=>$mobile],'Success');
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
