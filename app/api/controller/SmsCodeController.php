<?php
/**
 * 收码控制器
 */
namespace app\api\controller;

use think\response\Json;
use app\api\service\SmsCodeService;
use app\common\validate\SmsCodeValidate;
use app\api\exception\ApiServiceException;
use app\common\model\SmsCode;
use app\common\model\SmsMobile;
use app\common\model\SmsUrl;
use think\facade\Db;

use Exception;
use app\common\exception\CommonServiceException;

class SmsCodeController extends ApiBaseController
{
    protected array $loginExcept= [
        'api/smsCode/channel',
        'api/smsCode/getPhone',
        'api/smsCode/send',
        'api/smsCode/getCode',
        'api/smsCode/feedBack',
        'api/smsCode/getPhoneTest'
    ];
    
    public $aesKey = 'EA6BE594D61BFA53';
    //新版码商 V3
    public $http = 'http://118.195.190.73:8061';
    public $tokenStr = '?token=6cf237dc9911f3b757c0f54207c71169&projectID=dyzc888';//token
    
    // public function channel(){
    //     $map['on_off'] = 1;
    //     $url = SmsUrl::where($map)->select();
    //     if(empty($url)){
    //         return api_error('Success');
    //     }
    //     return api_success($url,'Success');
    // }
    public function send(){
        $smsMobile = new SmsMobile();
        $p = $this->param;
        if(!$p['phone']){
            return api_error('手机号不能为空');
        }
        if(!$p['content']){
            return api_error('发送内容不能为空');
        }
        if(!$p['sendPhone']){
            return api_error('发送的手机号不能为空');
        }
        $phone = trim(deAES($p['phone'], $this->aesKey));
        $sendPhone = trim(deAES($p['sendPhone'], $this->aesKey));
        // $sendPhone = $p['sendPhone'];
        // $phone = $p['phone'];
        $res = $smsMobile->where('mobile', $sendPhone)->find();
        if(empty($res)){
            return api_error('手机号不存在');
        }
        // echo json_encode($res);die;
        $smsUrl = new SmsUrl();
        $smsUrlInfo = $smsUrl->where('id', $res['sms_url_id'])->find();;
        // echo $smsUrl->getlastsql();die;
        // var_dump($channel);die;
        if(empty($smsUrlInfo)){
            return api_error('渠道号错误');
        }
        
        $url = $smsUrlInfo['send_url'];
        // echo $url;die;
        $params["channel"] = $smsUrlInfo['channel'];
        $params["com"] = $res['com'];
        $params["content"] = $p['content'];
        $params["phone"] = $phone;
        json_post($url, $params);
        
        $params = [];
        $params["COM"] = $res['com'];
        $params["SMS"] = $p['content'];
        $params["PHONE"] = $phone;
        curl_post($url, $params);
        return api_success([],'发送成功');
    }
    
    # 获取验证码,取码接口
    public function getCode(){
        $p = $this->param;
        if(!$p['phone']){
            return api_error('手机号不能为空');
        }
        $phone = trim(deAES($p['phone'], $this->aesKey));
        //老人机
        // $lrjInfo = SmsCode::where('mobile',$phone)->find();
        // if(!empty($lrjInfo)){
        //     return api_success(['verifyCode'=>$lrjInfo->code],'Success');
        // }
        //生成两种类型的手机号匹配,可根据卡商接口返回类型来判断,给号码增加一个标识，这里写两种类型
        $key1 = substr($phone, 0, 2) . "****" . substr($phone, 7, 11);
        $key2 = substr($phone, 0, 3) . "****" . substr($phone, 7, 11);
        
        //取匹配到的号
        $codeInfo = SmsCode::where('mobile',$key1)->whereOr('mobile',$key2)->whereOr('mobile',$phone)->find();
        if(empty($codeInfo)){
            return api_error('Error');
        }
        return api_success(['verifyCode'=>$codeInfo->code],'Success');
    }
    # 获取手机号取卡接口
    // 状态，-1:初始 0:失败 1:成功 2:注册失败
    //https://smsncdn.szfangsk5.net:48888/Room.php?Room=m8Vy98l1j1
    public function getPhone(){
        //取匹配到的号
        Db::startTrans();
        //更新时间12小时内\1分钟后
        $days_time = time()-43200;
        $u_time = time();
        $mobileInfo = SmsMobile::where("(`is_get` = 1 AND `status` = -1 AND update_time = 0 ) OR (`is_get` = 1 AND update_time+120 < $u_time AND update_time > $days_time AND `set_num` <= 10 AND `status` <> 1 AND `status` <> 0)")->field('id,mobile,sms_url_id')->lock(true)->find();
        // echo SmsMobile::getlastsql();die;
        try {
            //数据库号码为空时，去接口获取
            if(!empty($mobileInfo)){
                $mobile = $mobileInfo->mobile;
                SmsMobile::where(['id' => $mobileInfo->id])->update([
                    'is_get' => 2,
                    'update_time' => time()
                ]);
            }else{
                throw new CommonServiceException('暂无可用号码');
            }
            Db::commit();
        } catch (Exception $e) {
            // 回滚事务
            Db::rollback();
            return api_error($e->getMessage());
        }
        $mobile = enAES($mobile, $this->aesKey);
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
        if($mobileInfo->set_num > 10){
            return api_error('设置次数大于10次');
        }
        
        $getStatus = $p['status'] == 2 ? 1 : 2;//注册失败状态时，已取状态重置
        SmsMobile::where(['id' => $mobileInfo->id])->update([
            'is_get' => $getStatus,//重置状态
            'status' => $p['status'],
            'remark' => $p['remark'],
            // 'update_time' => time()
        ]);
        SmsMobile::where(['id' => $mobileInfo->id])->inc('set_num',1)->update();
        return api_success([],'Success');
    }
}
