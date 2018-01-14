<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2018/1/14
 * Time: 22:43
 */
require '../vendor/autoload.php';
$client = new Consatan\Weibo\ImageUploader\Client();
$username = 'prctrash@126.com';
$password = '0okmnji9';
while (true) {
    try {
        echo $client->upload('./images/nogizaka46_logo.jpg', $username, $password);
        break;
    } catch (Consatan\Weibo\ImageUploader\Exception\BadResponseException $e) {
        echo '验证码图片位置：' . $e->getMessage() . PHP_EOL .  '输入验证码以继续：';
        if (!$client->login($username, $password, stream_get_line(STDIN, 1024, PHP_EOL))) {
            echo '登入失败';
            break;
        }
    }
}