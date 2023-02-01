<?php
/**
 * 收码控制器
 */
namespace app\api\controller;

use think\response\Json;
use app\common\model\SmsUrl;
use app\common\model\mongo\WuxianCode;

class SmsController extends ApiBaseController
{
    protected array $loginExcept= [
        'api/sms/getCode',
    ];
    
    # 获取验证码,取码接口
    public function getCode(){
        $p = $this->param;
        if(empty($p['phone'])){
            return api_error('手机号不能为空');
        }
        // $phone = trim(deAES($p['phone'], $this->aesKey));
        //生成两种类型的手机号匹配,可根据卡商接口返回类型来判断,给号码增加一个标识，这里写两种类型
        // $key1 = substr($phone, 0, 2) . "****" . substr($phone, 7, 11);
        $key2 = substr($p['phone'], 0, 3) . "****" . substr($p['phone'], 7, 11);
        
        //取匹配到的号
        $codeInfo = WuxianCode::where('mobile',$key2)->find();
        // echo WuxianCode::getlastsql();die;
        // var_dump($codeInfo);die;
        if(empty($codeInfo)){
            return api_error('Error');
        }
        return api_success(['verifyCode'=>$codeInfo->code],'Success');
    }
}
