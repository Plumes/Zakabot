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
    $entityBody = $request->getContent();
    var_dump($entityBody);
    file_put_contents('test.txt', $entityBody);
    return "123";
});
