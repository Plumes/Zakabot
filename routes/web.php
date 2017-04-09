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
    $command = preg_match("/\/(\w+)/", $update['message']['text']);
    $param = preg_match("/\/\w+ (\w+)/",$update['message']['text']);

// compose reply
    $reply =  $update['message']['text'];

// send reply
    $sendto =$api_url."sendmessage?chat_id=".$chatID."&text=".$reply;
    file_get_contents($sendto);
    return "success";
});
