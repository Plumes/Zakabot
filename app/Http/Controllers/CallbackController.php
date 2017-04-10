<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2017/4/10
 * Time: 21:00
 */
namespace App\Http\Controllers;

use App\Libraries\TelegramAPI;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CallbackController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $update;
    private $callback_query_id;
    private $tg_user_id;

    public function __construct($update = false)
    {
        //
        $this->update = $update;
        $this->callback_query_id = $this->update['callback_query']['id'];
        $this->tg_user_id = $this->update['callback_query']['from']['id'];
    }

    public function subscribe($member_id) {
        $tg_api = new TelegramAPI();
        $now = Date('Y-m-d H:i:s');

        $member = DB::table('kyzk46_members')->where('id', intval($member_id))->first();
        $fan = DB::table('fans')->where('telegram_user_id', $this->tg_user_id)->first();
        if(!$member || !$fan) {
            return $tg_api->answerCallbackQuery($this->callback_query_id);
        }
        $check_data = DB::table('idol_fans_relation')
            ->where('fan_id', $fan->id)
            ->where('member_id', $member_id)
            ->first();
        $result = true;
        if(empty($check_data)) {
            $result = DB::table('idol_fans_relation')->insert([
                'fan_id'=>$fan->id,
                'member_id'=>intval($member_id),
                'created_at'=>$now,
                'updated_at'=>$now
            ]);
        }
        if($result===true) {
            $reply = "你成功订阅了 ".$member->name." 的日记";
        } else {
            $reply = "操作出现问题, 请稍后重试";
        }
        $tg_api->sendMessage($fan->chat_id, ['text'=>$reply]);
        return $tg_api->answerCallbackQuery($this->callback_query_id, ['text'=>$reply]);
    }

    public function unsubscribe($member_id) {
        $tg_api = new TelegramAPI();

        $member = DB::table('kyzk46_members')->where('id', intval($member_id))->first();
        $fan = DB::table('fans')->where('telegram_user_id', $this->tg_user_id)->first();
        if(!$member || !$fan) {
            return $tg_api->answerCallbackQuery($this->callback_query_id);
        }

        DB::table('idol_fans_relation')
            ->where('fan_id', $fan->id)
            ->where('member_id', $member->id)
            ->delete();

        $reply = "你成功退订了 ".$member->name." 的日记";

        $tg_api->sendMessage($fan->chat_id, ['text'=>$reply]);
        return $tg_api->answerCallbackQuery($this->callback_query_id, ['text'=>$reply]);
    }
}