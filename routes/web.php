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
            $cmd_class_name = "Start";
            break;
        default:
            $cmd_class_name = "";
            break;
    }

    $className = 'App\\Http\\TGCommands\\' . $cmd_class_name;
    $cmd_handler =  new $className;
    return $cmd_handler->handle($update);

});
