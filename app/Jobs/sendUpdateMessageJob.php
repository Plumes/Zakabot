<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2017/4/10
 * Time: 17:11
 */
namespace App\Jobs;

use Illuminate\Support\Facades\Log;

class sendUpdateMessageJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $chat_id;
    private $reply;
    private $cover_image;
    public function __construct($chat_id, $reply, $cover_image)
    {
        //
        $this->chat_id = $chat_id;
        $this->reply = $reply;
        $this->cover_image = $cover_image;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->cover_image ===  false) {
            return $this->sendText();
        } else {
            return $this->sendPhoto();
        }

    }

    private function sendText() {
        $api_url = "https://api.telegram.org/bot372178022:AAErVXV1vzhxF-tSgVgtwYzGe1DOzbXDSbg/sendmessage";
        $post_data = [
            'chat_id' => $this->chat_id,
            'text' => $this->reply,
            'parse_mode' => 'HTML'
        ];
        list($return_code, $return_content) = $this->http_post_data($api_url, json_encode($post_data));
        return "success";
    }

    private function sendPhoto() {
        $api_url = "https://api.telegram.org/bot372178022:AAErVXV1vzhxF-tSgVgtwYzGe1DOzbXDSbg/sendPhoto";
        $post_data = [
            'chat_id' => $this->chat_id,
            'photo' => $this->cover_image,
            'caption' => $this->reply
        ];
        list($return_code, $return_content) = $this->http_post_data($api_url, json_encode($post_data));
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