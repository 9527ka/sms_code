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

class SmsV3Controller extends ApiBaseController
{
    protected array $loginExcept= [
        'api/smsV3/checkPhone',
        'api/smsV3/getPhone',
        'api/smsV3/send',
        'api/smsV3/getCode',
        'api/smsV3/feedBack',
        'api/smsV3/sendCheckPhone',
        'api/smsV3/feedBackTest',
    ];
    
    public $http = 'http://118.195.190.73:8061';
    public $aesKey = 'EA6BE594D61BFA53';
    public $tokenStr = '?token=6cf237dc9911f3b757c0f54207c71169&projectID=dyzc888';//token
    
    # 获取手机号取卡接口
    public function getPhone(){
        $url = $this->http.'/api/getPhone'.$this->tokenStr;
        $res = http_curl($url);
        $res = json_decode($res);
        var_dump($res);die;
        if($res->code != 10000){
            return api_error($res->msg);
        }
        $phone = $res->phone;
        SmsMobile::insert([
            'sms_url_id' => '127',
            'mobile' => $phone,
            'create_time' => time()
        ]);
        // echo SmsMobile::getlastsql();die;
        return api_success(['phone'=>$phone],'Success');
        $phone = enAES($phone, $this->aesKey);
        return api_success(['phone'=>$phone],'Success');
    }
    
    # 获取验证码,取码接口
    public function getCode(){
        $p = $this->param;
        if(empty($p['phone'])){
            return api_error('手机号不能为空');
        }
        $url = $this->http.'/api/getPhoneSms?phone='.$phone.$this->tokenStr;
        $res = http_curl($url);
        $res = json_decode($res);
        // var_dump($res);die;
        if($res->code != 10000){
            return api_error($res->msg);
        }
        $phone = $res->phone;
        
        // SmsCode::insert([
        //     'mobile' => $phone,
        //     'code' => $res[1],
        //     'create_time' => time()
        // ]);
        return api_success(['verifyCode'=>$res->sms],'Success');
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
