<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2017/5/8
 * Time: 10:56
 */

namespace App\Libraries;

use Consatan;
use Illuminate\Support\Facades\DB;

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

    static public function uploadNGZKImage($post_id,$cdn_id,$original_url) {
        $img_url = "http://dcimg.awalker.jp/img1.php?id=".$cdn_id;
        $url_hash = md5($img_url);
        HTTPUtil::get($img_url, $url_hash);
        $img_url = "http://dcimg.awalker.jp/img2.php?sec_key=".$cdn_id;
        $img_file = HTTPUtil::get($img_url, $url_hash);
        if(empty($img_file)) return null;

        file_put_contents("/tmp/".$url_hash.".jpg", $img_file);
        $file_size = filesize("/tmp/".$url_hash.".jpg");
        $size = getimagesize("/tmp/".$url_hash.".jpg");


        // 默认返回的是 https 协议的图床 URL，调用该方法返回的是 http 协议的图床 URL
        // $weibo->useHttps(false);

        // 上传示例图片
        $weibo_client = new Consatan\Weibo\ImageUploader\Client();
        $url = $weibo_client->upload("/tmp/".$url_hash.".jpg", 'prctrash@126.com', '0okmnji9');

        if(file_exists("/tmp/".$url_hash)) unlink("/tmp/".$url_hash);
        if(file_exists("/tmp/".$url_hash.".jpg")) unlink("/tmp/".$url_hash.".jpg");

        // 输出新浪图床 URL
        if(empty($url)) return null;

        $img_data = [
            'post_id'=>$post_id,
            'original_url'=>$original_url,
            'original_url_hash'=>md5($original_url),
            'url'=>$url,
            'file_size'=>intval($file_size),
            'width'=>intval($size[0]),
            'height'=>intval($size[1]),
            'created_at'=>date('Y-m-d H:i:s'),
            'updated_at'=>date('Y-m-d H:i:s')
        ];
        DB::table('post_images')->insert($img_data);
        return ['url'=>$url,'file_size'=>$file_size,'size'=>$size,'original_url'=>$original_url];
    }
}