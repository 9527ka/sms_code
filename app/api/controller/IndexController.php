<?php
/**
 *
 * @author yupoxiong<i@yupoxiong.com>
 */

declare (strict_types=1);

namespace app\api\controller;
use QL\QueryList;
use think\response\Json;
use app\common\model\mongo\WuxianCode;

class IndexController extends ApiBaseController
{
    protected array $loginExcept= [
        'api/index/test',
    ];
    public function index(): Json
    {
        return api_success();
    }
    public function test()
    {
        $html = file_get_contents("http://47.94.157.44/Room.php?form=douyinrr");
        $list = explode("<tr>",$html);
        foreach ($list as $v){
            $str = str_replace("</td>","",$v);
            $str = str_replace("</tr>","",$str);
            $str = str_replace("\n","",$str);
            $tdArr = explode("<td>",$str);
            //手机号
            if(!empty($tdArr[2])){
                $phone = $tdArr[2];
                $code = explode("验证码", $tdArr[3])[1];
                $code = explode("，", $code)[0];
                if(!WuxianCode::where('mobile',$phone)->find()){
                    WuxianCode::insert([
                        'mobile' => trim($phone),
                        'code' => $code,
                        'create_time' => time()
                    ]);
                }
            }
        }
        die;
    }
}
