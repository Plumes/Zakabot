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

$app->post('/{bot_id}/hook', function ($bot_id) use ($app) {
    if($bot_id=="372178022") {
        $group_id = 1;
    } elseif ($bot_id=="309781356") {
        $group_id = 2;
    } else {
        return "error";
    }
    $content = file_get_contents("php://input");
    \Illuminate\Support\Facades\Log::info($content);
    $update = json_decode($content, true);
    if(isset($update['message']) && isset($update['message']['text'])) {

        preg_match("/\/(\w+)/", $update['message']['text'], $matches);
        if (!isset($matches[1])) {
            return "success";
        }
        $command = $matches[1];
        switch ($command) {
            case "start":
                $cmd_func_name = "start";
                break;
            case "sublist":
                $cmd_func_name = "subscribeList";
                break;
            case "unsublist":
                $cmd_func_name = "unsubscribeList";
                break;
            default:
                $cmd_func_name = "";
                break;
        }
        $webhook_controller = new App\Http\Controllers\WebhookController($bot_id, $group_id, $update);
        if (method_exists($webhook_controller, $cmd_func_name)) {
            return $webhook_controller->$cmd_func_name();
        }
    } elseif (isset($update['callback_query'])) {
       // \Illuminate\Support\Facades\Log::info(json_encode($update));
        if(isset($update['callback_query']['data'])) {
            preg_match('/(\w+)@(\w+)/', $update['callback_query']['data'], $matches);
            if(count($matches)==3) {
                $command = $matches[1];
                $param = $matches[2];
            }

            switch ($command) {
                case "sub":
                    $cmd_func_name = "subscribe";
                    break;
                case "unsub":
                    $cmd_func_name = "unsubscribe";
                    break;
                default:
                    $cmd_func_name = "";
                    break;
            }

            $webhook_controller = new App\Http\Controllers\CallbackController($bot_id, $group_id, $update);
            if (method_exists($webhook_controller, $cmd_func_name)) {
                return $webhook_controller->$cmd_func_name($param);
            } else {
                $tg_api = new \App\Libraries\TelegramAPI($bot_id);
                $tg_api->answerCallbackQuery($update['callback_query']['id']);
            }
        }
    }
    return "error";
});

$app->get('crawl', ['uses'=>'MainController@crawl']);