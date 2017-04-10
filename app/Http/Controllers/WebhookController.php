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
    private $tg_user_id;
    public function __construct($update=false)
    {
        //
        $this->update = $update;
        $this->chat_id = $this->update["message"]["chat"]["id"];
        $this->tg_user_id = $this->update["message"]['from']['id'];
    }

    //
    public function start() {
        $fan = DB::table('fans')->where('telegram_user_id', $this->tg_user_id)->first();
        if(!$fan && !empty($this->tg_user_id)) {
            DB::table('fans')->insert([
                'username' => $this->update["message"]['from']['first_name'],
                'telegram_user_id' => $this->tg_user_id,
                'chat_id' => $this->chat_id
            ]);
        }
        $tg_api = new TelegramAPI();
        $reply =  ['text'=>"欢迎使用"];
        return $tg_api->sendMessage($this->chat_id, $reply);
    }

    public function subscribeList() {
        $tg_api = new TelegramAPI();
        $fan = DB::table('fans')->where('telegram_user_id', $this->tg_user_id)->first();
        if(!$fan) {
            return "error";
        }
        $subscribed_member_id_list = DB::table('idol_fans_relation')->where('fan_id', $fan->id)->pluck('member_id');
        $subscribed_member_id_list[] = '-1';
        $other_member_list = DB::table('kyzk46_members')->whereNotIn('id', $subscribed_member_id_list)->get();
        if(count($other_member_list)<1) {
            $reply = ['text'=>"你已经关注了全部成员了"];
        } else {
            $inline_keyboard = [];
            $inline_keyboard_one_row = [];
            $i=0;
            foreach ($other_member_list as $member) {
                if($i==3) {
                    $i=0;
                    $inline_keyboard[] = $inline_keyboard_one_row;
                    $inline_keyboard_one_row = [];
                }

                $inline_keyboard_one_row[] = ['text'=>$member->name, 'callback_data'=>'sub@'.$member->id];
                $i++;
            }
            $inline_keyboard[] = $inline_keyboard_one_row;
            $reply = [
                'text' => "以下是你尚未订阅的成员列表，点击即可订阅",
                'reply_markup' => ['inline_keyboard'=>$inline_keyboard]
            ];
        }
        $tg_api->sendMessage($this->chat_id, $reply);
        return "success";
    }

    public function unsubscribeList() {
        $fan = DB::table('fans')->where('telegram_user_id', $this->tg_user_id)->first();
        if(!$fan) {
            return "error";
        }
        $tg_api = new TelegramAPI();
        $subscribed_member_id_list = DB::table('idol_fans_relation')->where('fan_id', $fan->id)->pluck('member_id');
        if(count($subscribed_member_id_list)<1) {
            $reply = ['text'=>"你还没有订阅成员"];
        } else {
            $subscribed_member_list = DB::table('kyzk46_members')->whereIn('id', $subscribed_member_id_list)->get();
            $inline_keyboard = [];
            $inline_keyboard_one_row = [];
            $i=0;
            foreach ($subscribed_member_list as $member) {
                if($i==3) {
                    $i=0;
                    $inline_keyboard[] = $inline_keyboard_one_row;
                    $inline_keyboard_one_row = [];
                }

                $inline_keyboard_one_row[] = ['text'=>$member->name, 'callback_data'=>'unsub@'.$member->id];
                $i++;
            }
            $inline_keyboard[] = $inline_keyboard_one_row;
            $reply = [
                'text' => "以下是已经订阅的成员列表，点击即可退订",
                'reply_markup' => ['inline_keyboard'=>$inline_keyboard]
            ];
        }
        $tg_api->sendMessage($this->chat_id, $reply);
        return "success";
    }
}