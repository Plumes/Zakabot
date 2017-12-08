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

}