<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->post('/hook', function (\Illuminate\Http\Request $request) use ($app) {
    $api_url = "https://api.telegram.org/bot372178022:AAErVXV1vzhxF-tSgVgtwYzGe1DOzbXDSbg/";
    $content = file_get_contents("php://input");
    $update = json_decode($content, true);
    $chatID = $update["message"]["chat"]["id"];
    preg_match("/\/(\w+)/", $update['message']['text'], $matches);
    $command = $matches[0];
    preg_match("/\/\w+ (\w+)/",$update['message']['text'], $matches);
    $param = $matches[0];

// compose reply
    $reply =  "你".$command."了".$param;

// send reply
    $sendto =$api_url."sendmessage?chat_id=".$chatID."&text=".$reply;
    file_get_contents($sendto);
    return "success";
});
