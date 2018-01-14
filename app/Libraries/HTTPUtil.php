<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2017/5/8
 * Time: 10:56
 */

namespace App\Libraries;

class HTTPUtil
{
    static public function get($url, $cookie_file_name=false) {
        //初始化
        $ch = curl_init();
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.36 Safari/537.36';
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        if($cookie_file_name!==false) {
            curl_setopt( $ch, CURLOPT_COOKIEJAR, "/tmp/".$cookie_file_name );
            curl_setopt( $ch, CURLOPT_COOKIEFILE, "/tmp/".$cookie_file_name );
        }

        //执行并获取HTML文档内容
        $output = curl_exec($ch);

        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        //释放curl句柄
        curl_close($ch);
        //打印获得的数据
        if($return_code=="200") {
            return $output;
        } else {
            return false;
        }
    }

    static public function post($url, $post_data) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.36 Safari/537.36';
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close ( $ch );
        if($return_code=="200") {
            return $return_content;
        } else {
            return false;
        }
    }

    static public function submitMIP($url_array) {
        $api = 'http://data.zz.baidu.com/urls?site=zakabot.zhh.me&token=SWwpysKMoRLH18ly&type=amp';
        $ch = curl_init();
        $options =  array(
            CURLOPT_URL => $api,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => implode("\n", $url_array),
            CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        echo $result;
    }
}