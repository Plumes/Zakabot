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
    public function __construct()
    {
        //
    }

    //
    public function start($update) {
        $api_url = "https://api.telegram.org/bot372178022:AAErVXV1vzhxF-tSgVgtwYzGe1DOzbXDSbg/";
        $chatID = $update["message"]["chat"]["id"];
        $reply =  "欢迎使用";

        $sendto =$api_url."sendmessage?chat_id=".$chatID."&text=".$reply;
        file_get_contents($sendto);
        return $chatID;
    }

    public function subscribe($update) {
        $api_url = "https://api.telegram.org/bot372178022:AAErVXV1vzhxF-tSgVgtwYzGe1DOzbXDSbg/";
        $chatID = $update["message"]["chat"]["id"];
        preg_match("/\/\w+ (\w+)/",$update['message']['text'], $matches);
        if(!isset($matches[1])) {
            $reply = "无法识别";
        } else {
            $param = $matches[1];
            $reply =  "你 subscribe 了".$param;
        }

        $sendto =$api_url."sendmessage?chat_id=".$chatID."&text=".$reply;
        file_get_contents($sendto);
        return $chatID;
    }
}