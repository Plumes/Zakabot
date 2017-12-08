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
        $content = file_get_contents("http://www.keyakizaka46.com/s/k46o/diary/member/list?ima=0000");
        preg_match("/blogUpdate = (\[.*\])/Us", $content, $matches);
        $result = $matches[1];
        $result = preg_replace("/\n/s", "", $result);
        $result = preg_replace("/member/", "\"member\"", $result);
        $result = preg_replace("/update/", "\"update\"", $result);
        $result = json_decode($result, true);
        if(empty($result)) return;
        $member_list = DB::table('idol_members')->where('group_id',1)->get();
        $member_last_post_list = [];
        foreach ($member_list as $member) {
            $member_last_post_list[intval($member->official_id)] = $member->last_post_at;
        }
        $member_last_post_list[1000] = "2017-01-01 00:00:00";//平假名二期特殊处理
        $i=0;
        var_dump($result);
        foreach ($result as $v) {
            preg_match('/(.*)\+/', $v['update'], $matches);
            $current_update_at = $matches[1];
            $current_update_at = preg_replace('/T/',' ', $current_update_at);

            if(isset($member_last_post_list[intval($v['member'])]) && $current_update_at.":00">$member_last_post_list[intval($v['member'])]) {
                print_r("craw kyzk ".$v['member']." current:".$current_update_at." last: ".$member_last_post_list[intval($v['member'])]."\n");
                //dispatch( (new getKYZKLatestPostJob($v['member']))->delay(5*$i++) );
            }

        }
        return "123";
    }

}