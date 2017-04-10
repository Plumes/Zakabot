<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2017/4/10
 * Time: 19:50
 */
namespace App\Libraries;

class TelegramAPI {
    private $api_base = "https://api.telegram.org/bot372178022:AAErVXV1vzhxF-tSgVgtwYzGe1DOzbXDSbg/";
    public function __construct()
    {
    }

    public function sendMessage($chat_id, $reply) {
        $api = $this->api_base."sendMessage";
        $reply['chat_id'] = $chat_id;
        list($return_code, $return_content) = $this->http_post_data($api, json_encode($reply));
        return "success";
    }

    public function answerCallbackQuery($callback_query_id, $optional_data=false) {
        $api = $this->api_base."answerCallbackQuery";
        $post_data = [];
        if(is_array($optional_data)) {
            $post_data = $optional_data;
        }
        $post_data['callback_query_id'] = $callback_query_id;
        list($return_code, $return_content) = $this->http_post_data($api, json_encode($post_data));
        return "success";
    }

    public function http_post_data($url, $data_string) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($data_string))
        );
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();

        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return array($return_code, $return_content);
    }
}