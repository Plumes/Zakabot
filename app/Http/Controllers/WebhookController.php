<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2017/4/10
 * Time: 1:22
 */
namespace App\Http\Controllers;

use App\Libraries\TelegramAPI;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $update;
    private $chat_id;
    public function __construct($update=false)
    {
        //
        $this->update = $update;
        $this->chat_id = $this->update["message"]["chat"]["id"];
    }

    //
    public function start() {
        $tg_api = new TelegramAPI();
        $reply =  ['text'=>"欢迎使用"];
        return $tg_api->sendMessage($this->chat_id, $reply);
    }

    public function subscribeList() {
        $tg_api = new TelegramAPI();
        $subscribed_member_id_list = DB::table('idol_fans_relation')->where('chat_id', $this->chat_id)->pluck('member_id');
        Log::info(json_encode($subscribed_member_id_list));
        $subscribed_member_id_list[] = '-1';
        $other_member_list = DB::table('kyzk46_members')->whereNotIn('id', $subscribed_member_id_list)->get();
        Log::info(json_encode($other_member_list));
        if(count($other_member_list)<1) {
            $reply = ['text'=>"你已经关注了全部成员了"];
        } else {
            $reply_markup = [];
            foreach ($other_member_list as $member) {
                $reply_markup[] = ['text'=>$member->name, 'callback_data'=>'sub@'.$member->id];
            }
            $reply = [
                'text' => "以下是你尚未订阅的成员列表，点击即可订阅",
                'reply_markup' => $reply_markup
            ];
        }
        $tg_api->sendMessage($this->chat_id, $reply);
        return "success";
    }

    public function unsubscribeList() {
        $tg_api = new TelegramAPI();
        $subscribed_member_id_list = DB::table('idol_fans_relation')->where('chat_id', $this->chat_id)->pluck('member_id');
        if(count($subscribed_member_id_list)<1) {
            $reply = ['text'=>"你还没有订阅成员"];
        } else {
            $subscribed_member_list = DB::table('kyzk46_members')->whereIn('id', $subscribed_member_id_list)->get();
            $reply_markup = [];
            foreach ($subscribed_member_list as $member) {
                $reply_markup[] = ['text'=>$member->name, 'callback_data'=>'unsub@'.$member->id];
            }
            $reply = [
                'text' => "以下是已经订阅的成员列表，点击即可退订",
                'reply_markup' => $reply_markup
            ];
        }
        $tg_api->sendMessage($this->chat_id, $reply);
        return "success";
    }

    public function subscribe() {

        preg_match("/\/\w+ (\w+)/",$this->update['message']['text'], $matches);
        $now = Date('Y-m-d H:i:s');
        if(!isset($matches[1])) {
            $reply = "无法识别";
        } else {
            $param = $matches[1];
            $member = DB::table('kyzk46_members')->where('id', intval($param))->first();
            if(empty($member)) {
                $reply = "错误指令";
                return $this->sendMessage($reply);
            }
            $fan = DB::table('fans')->where('chat_id', $this->chat_id)->first();
            $check_data = DB::table('idol_fans_relation')
                ->where('chat_id', $this->chat_id)
                ->where('member_id', $param)
                ->first();
            if(empty($fan)) {
                $fan_id = DB::table('fans')->insert([
                    'chat_id'=>$this->chat_id,
                    'username'=>$this->update['message']['from']['username'],
                    'created_at'=>$now,
                    'updated_at'=>$now
                ]);
            } else {
                $fan_id = $fan->id;
            }
            $result = true;
            if(empty($check_data)) {
                $result = DB::table('idol_fans_relation')->insert([
                    'fan_id'=>$fan_id,
                    'chat_id'=>$this->chat_id,
                    'member_id'=>intval($param),
                    'created_at'=>$now,
                    'updated_at'=>$now
                ]);
            }
            if($result===true) {
                $reply = "你成功订阅了 ".$member->name." 的日记";
            } else {
                $reply = "操作出现问题, 请稍后重试";
            }
        }
        return $this->sendMessage($reply);
    }

    public function unsubscribe() {
        preg_match("/\/\w+ (\w+)/",$this->update['message']['text'], $matches);
        if(!isset($matches[1])) {
            $reply = "无法识别";
        } else {
            $param = $matches[1];
            $member = DB::table('kyzk46_members')->where('id', intval($param))->first();
            if (empty($member)) {
                $reply = "错误指令";
                return $this->sendMessage($reply);
            }

            DB::table('idol_fans_relation')
                ->where('chat_id', $this->chat_id)
                ->where('member_id', $member->id)
                ->delete();

            $reply = "你成功退订了 ".$member->name." 的日记";

        }
        return $this->sendMessage($reply);
    }


}