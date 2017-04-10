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

$app->post('/hook', function () use ($app) {
    $content = file_get_contents("php://input");
    $update = json_decode($content, true);
    preg_match("/\/(\w+)/", $update['message']['text'], $matches);
    if(!isset($matches[1])) {
        return "success";
    }
    $command = $matches[1];
    switch ($command) {
        case "start":
            $cmd_func_name = "start";
            break;
        case "sub":
            $cmd_func_name = "subscribe";
            break;
        default:
            $cmd_func_name = "";
            break;
    }
    $webhook_controller = new App\Http\Controllers\WebhookController($update);
    if(method_exists($webhook_controller, $cmd_func_name)) {
        return $webhook_controller->$cmd_func_name();
    }
    return "error";
});

$app->get('crawl', ['uses'=>'MainController@crawl']);