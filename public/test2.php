<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2018/1/20
 * Time: 2:53
 */
require '../vendor/autoload.php';
use Illuminate\Container\Container as Container;
use Illuminate\Support\Facades\Facade as Facade;
use Illuminate\Support\Facades\DB;

$app = new Container();
$app->singleton('app', 'Illuminate\Container\Container');
$app->singleton('config', 'Illuminate\Config\Repository');

$app['config']->set('database.default', 'KYZKBot');
$app['config']->set('database.connections.KYZKBot', [
    'host'=>'127.0.0.1',
    'driver'   => 'mysql',
    'database' => 'KYZKBot',
    'username'=>"dbuser",
    'password'=>'123456qwe'
]);

$app->bind('db', function ($app) {
    return new \Illuminate\Database\DatabaseManager($app, new \Illuminate\Database\Connectors\ConnectionFactory($app));
});
Facade::setFacadeApplication($app);

ini_set('max_execution_time', 0); //0=NOLIMIT
set_time_limit(0);
//header( 'Content-type: text/html; charset=utf-8' );
//dispatch((new getNGZKMemberPost(1,201801, 0))->delay(1));
//dispatch( new sendEditMessage("309781356", "307558399", "21253", "test2") );
//$posts = DB::table('ngzk_posts')->select('id','title','content')->where('id','<',100)->orderBy('id','asc')->get();
DB::table('ngzk_posts')->orderBy('id','asc')->chunk(100, function ($posts) {
    foreach ($posts as $post) {
        //

        echo $post->id . " " . $post->title . "\n";
        preg_match_all("/http:\/\/[\w,\.\/]+(jpg|jpeg|png)/U", $post->content, $matches);
        foreach ($matches[0] as $k => $v) {
            $url_hash = md5($v);
            DB::table('ngzk_post_images')->where('original_url_hash', $url_hash)->update(['post_id' => $post->id]);
        }

    }
});