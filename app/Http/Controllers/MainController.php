<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2017/4/10
 * Time: 14:59
 */

namespace App\Http\Controllers;

use App\Jobs\getLatestPostJob;
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
        $member_id = "01";
        $fans_chat_list = DB::table('idol_fans_relation')->where('member_id', intval($member_id))->get();
        $reply = 'test 发表了新的日记 <b>1234</b><a href=\"http://www.baidu.com\">查看详情</a>';
        $i=0;
        foreach ($fans_chat_list as $chat) {
            dispatch(new sendUpdateMessageJob($chat->chat_id, $reply))->delay(1*($i++%10));
        }
        return "success";
    }
}