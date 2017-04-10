<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2017/4/10
 * Time: 1:22
 */
namespace App\Http\Controllers;

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
        $reply =  "欢迎使用";
        return $this->sendMessage($reply);
    }

    public function subscribe() {

        preg_match("/\/\w+ (\w+)/",$this->update['message']['text'], $matches);
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
                    'username'=>$this->update['message']['from']['username']
                ]);
            } else {
                $fan_id = $fan->id;
            }
            $result = true;
            if(empty($check_data)) {
                $result = DB::table('idol_fans_relation')->insert([
                    'fan_id'=>$fan_id,
                    'chat_id'=>$this->chat_id,
                    'member_id'=>intval($param)
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
            $result = DB::table('idol_fans_relation')
                ->where('chat_id', $this->chat_id)
                ->where('member_id', $member->id)
                ->delete();
            if($result===true) {
                $reply = "你成功退订了 ".$member->name." 的日记";
            } else {
                $reply = "操作出现问题, 请稍后重试";
            }
        }
        return $this->sendMessage($reply);
    }

    private function sendMessage($reply) {
        $api_url = "https://api.telegram.org/bot372178022:AAErVXV1vzhxF-tSgVgtwYzGe1DOzbXDSbg/";
        $sendto =$api_url."sendmessage?chat_id=".$this->chat_id."&text=".$reply;
        file_get_contents($sendto);
        return "success";
    }
}