<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2017/4/10
 * Time: 0:55
 */
namespace App\Http\TGCommands;

class Start {
    private $name = "start";
    public function __construct()
    {
    }

    public function handle($update) {
        $api_url = "https://api.telegram.org/bot372178022:AAErVXV1vzhxF-tSgVgtwYzGe1DOzbXDSbg/";
        $chatID = $update["message"]["chat"]["id"];
        preg_match("/\/\w+ (\w+)/",$update['message']['text'], $matches);
        if(!isset($matches[1])) {
            $reply = "无法识别";
        } else {
            $param = $matches[1];
            $reply =  "你".$this->name."了".$param;
        }

        $sendto =$api_url."sendmessage?chat_id=".$chatID."&text=".$reply;
        file_get_contents($sendto);
        return $chatID;
    }
}