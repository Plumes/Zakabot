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
        $reply =  "欢迎使用";

        $sendto =$api_url."sendmessage?chat_id=".$chatID."&text=".$reply;
        file_get_contents($sendto);
        return $chatID;
    }
}