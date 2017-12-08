<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2017/4/10
 * Time: 14:59
 */

namespace App\Http\Controllers;

use App\Jobs\getKYZKLatestPostJob;
use App\Jobs\sendUpdateMessageJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Libraries\HTTPUtil;

class MainController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    //
    public function crawl() {
        $result = DB::table("idol_fans_relation")
            ->select(DB::raw("DISTINCT(fan_id) as fan_id"))
            ->where('member_id','<',34)
            ->get();
        $fan_id_list = [];
        foreach($result as $v) {
            $fan_id_list[] = $v->fan_id;
        }
        $kyzk_fans = DB::table('fans')
            ->select('chat_id')
            ->whereIn('id', $fan_id_list)
            ->get();
        $reply_content = "非常抱歉，在官网更新加入平假名二期数据后，没有及时更新，导致故障了一段时间，现在已经修复，并加入了假名二期支持，欢迎订阅";
        foreach ($kyzk_fans as $fan) {
            dispatch( (new sendUpdateMessageJob("372178022", $fan->chat_id, $reply_content, false)) );
        }
    }
    public function test() {
        $content = file_get_contents("http://blog.nogizaka46.com/third/2017/12/042080.php");
        preg_match('/<a href="http:\/\/dcimg\.awalker\.jp\/img1\.php\?id=(\w+)".*<img/sU', $content, $matches);
        if(isset($matches[1])) {
            $img_url = "http://dcimg.awalker.jp/img1.php?id=".$matches[1];
            $url_hash = md5($img_url);
            HTTPUtil::get($img_url, $url_hash);
            $img_url = "http://dcimg.awalker.jp/img2.php?sec_key=".$matches[1];
            $img_file = HTTPUtil::get($img_url, $url_hash);
            if($img_file!=false) {
                file_put_contents("/tmp/".$url_hash.".jpg", $img_file);
                $result = HTTPUtil::post("https://sm.ms/api/upload", ['smfile'=>curl_file_create("/tmp/".$url_hash.".jpg")]);
                if($result!==false) {
                    $result = json_decode($result, true);
                    if(isset($result['data']['url'])) {
                        $cover_image = $result['data']['url'];
                        $cover_image_hash = $result['data']['hash'];
                    }
                }
            }
            var_dump($cover_image);
            if(file_exists("/tmp/".$url_hash)) unlink("/tmp/".$url_hash);
            if(file_exists("/tmp/".$url_hash.".jpg")) unlink("/tmp/".$url_hash.".jpg");

        }
    }

}