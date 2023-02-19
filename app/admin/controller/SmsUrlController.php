<?php
/**
 * 渠道链接控制器
 */

namespace app\admin\controller;

use Exception;
use think\Request;
use think\db\Query;
use think\response\Json;
use app\common\model\SmsUrl;
use app\common\model\SmsMobile;

use app\common\validate\SmsUrlValidate;

class SmsUrlController extends AdminBaseController
{
    /**
     * 列表
     * @param Request $request
     * @param SmsUrl $model
     * @return string
     * @throws Exception
     */
    public function index(Request $request, SmsUrl $model, SmsMobile $smsMobile): string
    {
        $param = $request->param();
        $data  = $model->scope('where', $param)
            ->paginate([
                'list_rows' => $this->admin['admin_list_rows'],
                'var_page'  => 'page',
                'query'     => $request->get(),
            ]);
        if(!empty($data)){
            foreach ($data as $k => $v){
                $data[$k]['mobile_num'] = $smsMobile->where('sms_url_id',$v['id'])->count();
                $map = [];
                $map[] = ['sms_url_id','=',$v['id']];
                $map[] = ['status','=',1];
                $data[$k]['success_num'] = $smsMobile->where($map)->count();
            }
        }
        // var_dump($data);die;
        // 关键词，排序等赋值
        $this->assign($request->get());

        $this->assign([
            'data'  => $data,
            'page'  => $data->render(),
            'total' => $data->total(),
            
        ]);
        return $this->fetch();
    }

    /**
     * 添加
     * @param Request $request
     * @param SmsUrl $model
     * @param SmsUrlValidate $validate
     * @return string|Json
     * @throws Exception
     * // http://sms.szfangmm.com:3000/zTfY9ognfTZx9EenqZkrSD/send
    // http://sms.szfangmm.com:3000/5ZFtm6onsAq4W495hKX4Xi
    
    // http://sms.szfangmm.com:3000/api/send
    // http://sms.szfangmm.com:3000/api/smslist?token=5ZFtm6onsAq4W495hKX4Xi
    
    // https://smsncdn.szfangsk5.net:48888/SendRoom.php?Room=Vg9wVc7TJl
    // https://smsncdn.szfangsk5.net:48888/Room.php?Room=7w8uus89vV
     */
    public function add(Request $request, SmsUrl $model, SmsUrlValidate $validate)
    {
        if ($request->isPost()) {
            $param           = $request->param();
            $validate_result = $validate->scene('admin_add')->check($param);
            if (!$validate_result) {
                return admin_error($validate->getError());
            }
            $sta = $model->where('channel_name', $param['channel_name'])->find();
            if($sta){
                return admin_error("渠道名称重复");
            }
            /*******************格式兼容 End**********************/
            /*
            收：https://smsncdn.szfangsk5.net:48888/Room.php?Room=nyoy2OjRDa
            发：http://sms.szfangmm.com:3000/reMTR4Lj7thZ4UBSu2vkQc/send
            
            发： https://smsncdn.szfangsk5.net:48888/SendRoom.php?Room=7kAMiHgYZ1
            收： http://sms.szfangmm.com:3000/api/smslist?token=yWEK8pnpMmy45aHDVgFbpX
            
            https://smsncdn.szfangsk5.net:48888/SendRoom.php?Room=bR3U1AUD41
            http://sms.szfangmm.com:3000/8RWoWsJW4qdMrzh6ccJ2zJ
            */
            if(strpos($param['send_url'], 'sms.szfangmm.com') && strpos($param['receive_url'], 'smsncdn.szfangsk5.net')){
                $send_url = str_replace("/send","",$param['send_url']);//剔除/send
                $send_url = explode(':3000/', $send_url);
                // var_dump($send_url);die;
                $param['channel'] = trim($send_url[1]);//token
                
                $receive_url = explode('?Room=', $param['receive_url']);
                $param['receive_url'] = 'https://smsncdn.szfangsk5.net:48888/listforjson.php?Room='.trim($receive_url[1]);
                $param['send_url'] = 'http://sms.szfangmm.com:3000/api/send';
            }else if(strpos($param['send_url'], 'smsncdn.szfangsk5.net') && strpos($param['receive_url'], 'sms.szfangmm.com')){
                $send_url = explode('?Room=', $param['send_url']);
                $param['channel'] = trim($send_url[1]);//token
                
                $receive_url = explode('com:3000/', $param['receive_url']);
                $param['receive_url'] = 'http://sms.szfangmm.com:3000/api/smslist?token='.trim($receive_url[1]);
            }else{
                //http://sms.newszfang.vip:3000/NHiSa4Rpm57qEeDojNENjk/send
                //http://sms.newszfang.vip:3000/LnHPvdLfrUBpuPgXBL7EZg
                
                if(strpos($param['send_url'], 'sms.newszfang.vip')){
                    $send_url = str_replace("/send","",$param['send_url']);
                    $send_url = explode(':3000/', $send_url);
                    $param['send_url'] = 'http://sms.newszfang.vip:3000/api/send';
                    $param['channel'] = trim($send_url[1]);//token
                    
                    $receive_url = explode(":3000/", $param['receive_url']);
                    $param['receive_url'] = 'http://sms.newszfang.vip:3000/api/smslist?token='.$receive_url[1];
                }
                
                //sms.szfangmm.com
                if(strpos($param['send_url'], 'sms.szfangmm.com')){
                    $send_url = str_replace("/send","",$param['send_url']);
                    $send_url = explode(':3000/', $send_url);
                    $param['send_url'] = 'http://sms.szfangmm.com:3000/api/send';
                    $param['channel'] = trim($send_url[1]);//token
                    
                    $receive_url = explode(":3000/", $param['receive_url']);
                    $param['receive_url'] = 'http://sms.szfangmm.com:3000/api/smslist?token='.$receive_url[1];
                }
                
                //smsncdn.szfangsk5.net
                if(strpos($param['send_url'], 'smsncdn.szfangsk5.net')){
                    $receive_url = explode('Room=', $param['receive_url']);
                    $param['receive_url'] = 'https://smsncdn.szfangsk5.net:48888/listforjson.php?Room='.trim($receive_url[1]);
                }
            }
            
            $com_phone = $param['com_phone'];
            //是否存在空格
            $isKong = 0;
            if(strpos($com_phone, ',')){
                $com_phone = str_replace(" ",",",$com_phone);
            }else{
                if(strpos($com_phone, ' ')){
                    // $com_phone = str_replace(" "."\n","\n",$com_phone);
                    $isKong = 1;
                }else{
                    //统一将空格、----分隔形式的改为,分隔
                    $com_phone = str_replace(" ",",",$com_phone);
                    $com_phone = str_replace("----",",",$com_phone);
                }
            }
            
            /*******************格式兼容 End**********************/
            unset($param['com_phone']);
            unset($param['__token__']);
            $param['create_time'] = time();
            $result = $model::insert($param);
            if(!$result){
                return admin_error();
            }
            $sms_url_id = $model->getlastInsID();
            
            $smsMobile = new SmsMobile();
            $phoneList = explode("\n", $com_phone);
            $haveArr = $haveChannelArr = [];
            for ($i = 0; $i < count($phoneList); $i++) {
                
                $add = [];
                //拆分: com231,18855225522
                //按空格
                if($isKong == 1){
                    $info = explode(' ',$phoneList[$i]);
                }else{
                    //按逗号
                    $info = explode(',',$phoneList[$i]);
                }
                if(!is_numeric($info[1])){
                    return admin_error("号码格式错误");
                }
                //有重复导入的剔除，并加入提示数组
                $sta = $smsMobile->where('mobile', trim($info[1]))->find();
                if($sta){
                    array_push($haveChannelArr, $sta['sms_url_id']);
                    array_push($haveArr, $info[1]);
                    continue;
                }
                //获取com
                $add['com'] = trim(explode('COM',$info[0])[1]);
                //获取手机号
                $add['mobile'] = trim($info[1]);
                $add['sms_url_id'] = $sms_url_id;
                // var_dump($add);die;
                // $add['is_get'] = 2;
                $smsMobile->insert($add);
            }
            
            $url = URL_BACK;
            if (isset($param['_create']) && (int)$param['_create'] === 1) {
               $url = URL_RELOAD;
            }
            //有重复导入提示
            if(!empty($haveArr)){
                $haveText = implode("<br>",$haveArr);
                $haveChannelArr = array_unique($haveChannelArr);
                $channel_str = implode(',',$haveChannelArr);
                $smsUrlinfo = $model->where('id', 'in', $channel_str)->field('GROUP_CONCAT(channel_name) as channel_name_str')->find();
                
                return admin_success("添加成功，但有重复号码<br>".$haveText."<br>所属渠道：".$smsUrlinfo->channel_name_str, $haveArr, $url, 300);
            }
            return admin_success('添加成功', [], $url);
        }
        
        return $this->fetch();
    }

    /**
     * 修改
     * @param $id
     * @param Request $request
     * @param SmsUrl $model
     * @param SmsUrlValidate $validate
     * @return string|Json
     * @throws Exception
     */
    public function edit($id, Request $request, SmsUrl $model, SmsUrlValidate $validate)
    {
        $data = $model->findOrEmpty($id);
        if ($request->isPost()) {
            $param = $request->param();
            $check = $validate->scene('admin_edit')->check($param);
            if (!$check) {
                return admin_error($validate->getError());
            }
            
            $result = $data->save($param);
            if(!empty($param['com_phone'])){
                /*******************格式兼容 End**********************/
                // if(strpos($param['send_url'], 'smsncdn.szfangsk5.net') && strpos($param['receive_url'], 'sms.szfangmm.com')){
                //     $receive_url = explode('com:3000/', $param['receive_url']);
                //     $param['send_url'] = 'https://smsncdn.szfangsk5.net:48888/SendRoom.php?Room=7kAMiHgYZ1';
                //     $param['receive_url'] = 'https://smsncdn.szfangsk5.net:48888/listforjson.php?Room='.$receive_url[1];
                // }else{
                //     //sms.szfangmm.com
                //     if(strpos($param['send_url'], 'sms.szfangmm.com')){
                //         $send_url = str_replace("/send","",$param['send_url']);
                //         $send_url = explode(':3000/', $send_url);
                //         $param['send_url'] = 'http://sms.szfangmm.com:3000/api/send';
                //         $param['channel'] = $send_url[1];//token
                        
                //         $receive_url = explode(":3000/", $param['receive_url']);
                //         $param['receive_url'] = 'http://sms.szfangmm.com:3000/api/smslist?token='.$receive_url[1];
                //     }
                    
                //     //smsncdn.szfangsk5.net
                //     if(strpos($param['send_url'], 'smsncdn.szfangsk5.net')){
                //         $receive_url = explode('Room=', $param['receive_url']);
                //         $param['receive_url'] = 'https://smsncdn.szfangsk5.net:48888/listforjson.php?Room='.$receive_url[1];
                //     }
                // }
                $com_phone = $param['com_phone'];
                $mobileArr = $channelArr = [];//重复号码、重复号码所在渠道
                //江苏无限码，纯手机号格式录入
                if($id == 126){
                    $arr = $this->_savePhone($com_phone, $id);
                }else{
                    $arr = $this->_saveComPhone($com_phone, $id);
                }
                if(!empty($arr['code'])){
                    return admin_error($arr['msg']);
                }
                $mobileArr = $arr['mobileArr'];
                $channelArr = $arr['channelArr'];
                unset($param['com_phone']);
                unset($param['__token__']);
                // $param['create_time'] = time();
                $url = URL_BACK;
                if (isset($param['_create']) && (int)$param['_create'] === 1) {
                   $url = URL_RELOAD;
                }
                //有重复导入提示
                if(!empty($mobileArr)){
                    $haveText = implode("<br>",$mobileArr);
                    $channelArr = array_unique($channelArr);
                    $channel_str = implode(',',$channelArr);
                    $smsUrlinfo = $model->where('id', 'in', $channel_str)->field('GROUP_CONCAT(channel_name) as channel_name_str')->find();
                    return admin_success("修改成功，但有重复号码<br>".$haveText."<br>所属渠道：".$smsUrlinfo->channel_name_str, $mobileArr, $url, 300);
                }
            }

            return $result ? admin_success('修改成功', [], URL_BACK) : admin_error('修改失败');
        }

        $this->assign([
            'data' => $data,
        ]);

        return $this->fetch('add');
    }
    
    //江苏无限码格式：纯号码
    private function _savePhone($com_phone, $id){
        /*******************格式兼容 End**********************/
        $smsMobile = new SmsMobile();
        $phoneList = explode("\n", $com_phone);
        $mobileArr = $channelArr = [];
        for ($i = 0; $i < count($phoneList); $i++) {
            $add = [];
            //有重复导入的剔除，并加入提示数组
            $mobile = trim($phoneList[$i]);
            if(!is_numeric($mobile)){
                return ['code' => 1001, 'msg' => $mobile.'号码格式错误'];
                break;
            }
            $sta = $smsMobile->where('mobile', $mobile)->find();
            if($sta){
                array_push($channelArr, $sta['sms_url_id']);
                array_push($mobileArr, $mobile);
                continue;
            }
            $add['com'] = '';
            //获取手机号
            $add['mobile'] = $mobile;
            $add['sms_url_id'] = $id;
            $smsMobile->insert($add);
        }
        return ['mobileArr' => $mobileArr, 'channelArr' => $channelArr];
    }
    
    //非无限码格式：COM13,18888888888
    private function _saveComPhone($com_phone, $id){
        //是否存在空格
        $isKong = 0;
        if(strpos($com_phone, ',')){
            $com_phone = str_replace(" ",",",$com_phone);
        }else{
            if(strpos($com_phone, ' ')){
                // $com_phone = str_replace(" "."\n","\n",$com_phone);
                $isKong = 1;
            }else{
                //统一将空格、----分隔形式的改为,分隔
                $com_phone = str_replace(" ",",",$com_phone);
                $com_phone = str_replace("----",",",$com_phone);
            }
        }
        
        /*******************格式兼容 End**********************/
        $smsMobile = new SmsMobile();
        $phoneList = explode("\n", $com_phone);
        $mobileArr = $channelArr = [];
        for ($i = 0; $i < count($phoneList); $i++) {
            $add = [];
            //拆分: com231,18855225522
            //按空格
            if($isKong == 1){
                $info = explode(' ',$phoneList[$i]);
            }else{
                //按逗号
                $info = explode(',',$phoneList[$i]);
            }
            //有重复导入的剔除，并加入提示数组
            $mobile = trim($info[1]);
            if(!is_numeric($mobile)){
                return ['code' => 1001, 'msg' => $mobile.'号码格式错误'];
            }
            $sta = $smsMobile->where('mobile', $mobile)->find();
            if($sta){
                array_push($channelArr, $sta['sms_url_id']);
                array_push($mobileArr, $mobile);
                continue;
            }
            //获取com
            $add['com'] = trim(explode('COM',$info[0])[1]);
            //获取手机号
            $add['mobile'] = $mobile;
            $add['sms_url_id'] = $id;
            // var_dump($add);die;
            // $add['is_get'] = 2;
            $smsMobile->insert($add);
        }
        return ['mobileArr' => $mobileArr, 'channelArr' => $channelArr];
    }

    /**
     * 删除
     * @param mixed $id
     * @param SmsUrl $model
     * @return Json
     */
    public function del($id, SmsUrl $model, SmsMobile $smsMobile): Json
    {
        $check = $model->inNoDeletionIds($id);
        if(is_array($id)){
            for ($i = 0; $i< count($id); $i++){
                $smsMobile->where('sms_url_id',$id[$i])->delete();
                // $count = $smsMobile->where('sms_url_id',$id[$i])->delete();
                // if($count > 0){
                //     return admin_error('渠道ID:'.$id[$i].'不能被删除，该渠道下有'.$count.'条数据');
                // }
            }
        }else{
            $smsMobile->where('sms_url_id',$id)->delete();
        }
        
        if (false !== $check) {
            return admin_error('ID为' . $check . '的数据不能被删除');
        }

        $result = $model::destroy(static function ($query) use ($id) {
            /** @var Query $query */
            $query->whereIn('id', $id);
        });

        return $result ? admin_success('删除成功', [], URL_RELOAD) : admin_error('删除失败');
    }
    
    public function onOff(Request $request, SmsUrl $model)
    {
        $param = $request->param();
        $param = $request->param();
        $on_off = $param['on_off'] == 1 ? 2 : 1;
        $result = SmsUrl::where('id',$param['id'])->update(['on_off'=>$on_off]);
        header("Location: /admin/smsUrl/index");
        exit();
    }
}
