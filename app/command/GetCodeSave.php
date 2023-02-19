<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use app\common\model\SmsCode;
use app\common\model\SmsUrl;

class GetCodeSave extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('getCodeSave')
            ->setDescription('the getCodeSave command');
    }

    protected function execute(Input $input, Output $output)
    {
        $map[] = ['delete_time', '=', 0];
        $map[] = ['send_url', '<>', ''];
        $map[] = ['id', '<>', 126];
        $map[] = ['on_off', '=', 1];
        $url_list = SmsUrl::where($map)->select()->toArray();
        if(empty($url_list)){
            return 'url_list is empty';
        }
        // var_dump($url_list);die;
        foreach ($url_list as $v){
            $url = $v['receive_url'];
            // echo $url."\n";
            $res = http_curl($url);
            if(empty($res)) continue;
            $arr = json_decode($res, true);
            if(empty($arr)) continue;
            echo json_encode($arr)."\n";
            # 存储验证码,存数据库
            $res = array();
            
            foreach ($arr as $js){
                $msg = isset($js["msg"]) ? $js["msg"] : $js["content"];
                if (!strpos($msg, "抖音") || strpos($msg, "你的抖音号")){
                    continue;
                }
                if (strpos($msg, "若非本人操作")){
                    $msg = explode("验证码", $msg)[1];
                    $msg = explode("。", $msg)[0];
                } else {
                    $msg = explode("验证码", $msg)[1];
                    $msg = explode("，", $msg)[0];
                }
                $val = [];
                $val["msg"] = $msg;//验证码
                if (isset($js["com"])){
                    $val["com"] = $js["com"];
                }
                $val["phone"] = isset($js["phoneNum"]) ? $js["phoneNum"] : $js["simnum"];
            
                //key保存为数据库字段
                $key = $val["phone"];
                if (isset($val["com"])){
                    $key = $key."-".$val["com"];
                }
                
                //创建时间
                $time = isset($js['updatetime']) ? $js['updatetime'] : $js['time'];
                $time = $time ? strtotime($time) : time();
                $res[$key] = $val;
                $code = SmsCode::where(['mobile' => $val['phone']])->find();
                //手机号 验证码记录是否存在
                if($code){
                    if($code['create_time'] <= $time){
                        SmsCode::where(['mobile' => $val['phone']])->update([
                            'code' => $val['msg'],
                            'create_time' => $time
                        ]);
                    }
                }else{
                    SmsCode::insert([
                        'code' => $val['msg'],
                        'mobile' => $val['phone'],
                        'create_time' => $time
                    ]);
                }
            }
        }
        // 指令输出
        // $output->writeln('success');
    }
}
