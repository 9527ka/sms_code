<?php
// 应用公共文件


use think\facade\Config;

if (!function_exists('setting')) {
    /**
     * 设置相关助手函数
     * @param string|array $name
     * @param null $value
     * @return array|bool|mixed|null
     */
    function setting($name = '', $value = null)
    {
        if ($value === null && is_string($name)) {
            //获取一级配置
            if ('.' === substr($name, -1)) {
                $result = Config::get(substr($name, 0, -1));
                if (count($result) === 0) {
                    //如果文件不存在，查找数据库
                    $result = get_database_setting(substr($name, 0, -1));
                }

                return $result;
            }
            //判断配置是否存在或读取配置
            if (0 === strpos($name, '?')) {
                $result = Config::has(substr($name, 1));
                if (!$result) {
                    //如果配置不存在，查找数据库
                    if ($name && false === strpos($name, '.')) {
                        return [];
                    }

                    if ('.' === substr($name, -1)) {

                        return get_database_setting(substr($name, 0, -1));
                    }

                    $name_arr    = explode('.', $name);
                    $name_arr[0] = strtolower($name_arr[0]);

                    $result = get_database_setting($name_arr[0]);
                    if ($result) {
                        $config = $result;
                        // 按.拆分成多维数组进行判断
                        foreach ($name_arr as $val) {
                            if (isset($config[$val])) {
                                $config = $config[$val];
                            } else {
                                return null;
                            }
                        }

                        return $config;

                    }
                    return $result;
                }

                return true;
            }

            $result = Config::get($name);
            if (!$result) {
                $result = get_database_setting($name);
            }
            return $result;
        }
        return Config::set($name, $value);
    }

}

if (!function_exists('get_database_setting')) {
    /**
     * 获取数据库配置
     * @param $name
     * @return array
     */
    function get_database_setting($name): array
    {
        $result = [];
        $group  = (new app\common\model\SettingGroup)->where('code', $name)->findOrEmpty();
        if (!$group->isEmpty()) {
            $result = [];
            foreach ($group->setting as $key => $setting) {
                $key_setting = [];
                foreach ($setting->content as $content) {
                    $key_setting[$content['field']] = $content['content'];
                }
                $result[$setting->code] = $key_setting;
            }
        }

        return $result;
    }
}


if (!function_exists('format_size')) {
    /**
     * 格式化文件大小单位
     * @param $size
     * @param string $delimiter
     * @return string
     */
    function format_size($size, string $delimiter = ''): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 5; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . $delimiter . $units[$i];
    }
}

if(!function_exists('htmlentities_view')){
    /**
     * 封装默认的 htmlentities 函数，避免在php8.1环境中view传入null报错
     * @param mixed $string
     * @return string
     */
    function htmlentities_view($string): string
    {
        return htmlentities((string)$string);
    }
}

function http_curl($url, $params = false, $ispost = 0, $https = 0)
{
    // $httpInfo = array();
    // $headers['CLIENT-IP'] = '1.117.13.87'; 
    // $headers['X-FORWARDED-FOR'] = '1.117.13.87';
    // $headerArr = array(); 
    // foreach( $headers as $n => $v ) { 
    //     $headerArr[] = $n .':' . $v;  
    // }
    $ch = curl_init();
    //curl_setopt ($ch, CURLOPT_HTTPHEADER , $headerArr );  //构造IP
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($https) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
    }
    if ($ispost) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_URL, $url);
    } else {
        if ($params) {
            if (is_array($params)) {
                $params = http_build_query($params);
            }
            curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
    }

    $response = curl_exec($ch);
    if ($response === FALSE) {
        return false;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //$httpInfo = array_merge($httpInfo, curl_getinfo($ch));
    curl_close($ch);
    return $response;
}

function json_post($url, $data = NULL)
{

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    if(!$data){
        return 'data is null';
    }
    if(is_array($data))
    {
        $data = json_encode($data);
    }
    // echo $data;die;
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER,array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($data),
            'Cache-Control: no-cache',
            'Pragma: no-cache'
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($curl);
    $errorno = curl_errno($curl);
    if ($errorno) {
        return $errorno;
    }
    curl_close($curl);
    return $res;
}

function curl_post($url, $post) {

    $options = array(

    CURLOPT_RETURNTRANSFER => true,
    
    CURLOPT_HEADER => false,
    
    CURLOPT_POST => true,
    
    CURLOPT_POSTFIELDS => $post,
    
    );
    
    $ch = curl_init($url);
    
    curl_setopt_array($ch, $options);
    
    $result = curl_exec($ch);
    
    curl_close($ch);
    
    return $result;

}

function enAES($originTxt, $key): string{
    return base64_encode(openssl_encrypt($originTxt, 'AES-128-ECB',$key, OPENSSL_RAW_DATA));
}
function deAES($originTxt, $key): string{
    $data = base64_decode($originTxt);
    return openssl_decrypt($data,'AES-128-ECB',$key, OPENSSL_RAW_DATA);
}
