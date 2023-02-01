<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class GetCodeDouyinrr extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('getCodeDouyinrr')
            ->setDescription('the getCodeDouyinrr command');
    }

    protected function execute(Input $input, Output $output)
    {
        // $url = "http://47.94.157.44/Room.php?form=douyinrr";
        // $html = file_get_contents($url);
        $html = '<html><head><link href="https://smsncdn.szfangsk5.net:48888/css/bootstrap.min.css" rel="stylesheet" media="screen">  
        </head><body><table class="table table-responsive table-hover table-bordered" id="tableid">
        <tbody><tr>
    <th>时间</th>
    <th>手机号</th>
	<th>内容</th>
	<th>来自</th>
    </tr>
    <tr>
      <td>2023-01-29 18:36:39</td>
      <td>175****1553</td>
      <td>【抖音】验证码7989，用于手机验证码登录，5分钟内有效。验证码提供给他人可能导致账号被盗，请勿泄露，谨防被骗。</td>
      <td>抖音</td>
    </tr>
    <tr>
      <td>2023-01-29 18:36:11</td>
      <td>155****2443</td>
      <td>【抖音】验证码4802，用于换绑手机，5分钟内有效。验证码提供给他人可能导致账号被盗，请勿泄露，谨防被骗。</td>
      <td>抖音</td>
    </tr>
  </tbody></table></body></html>';
        //解决中文乱码
        //$getcontent = iconv("gb2312", "utf-8",$html);
        echo "<textarea style='width:800px;height:600px;'>".$html."</textarea>";
        // 指令输出
        $output->writeln('getCodeDouyinrr');
    }
}
