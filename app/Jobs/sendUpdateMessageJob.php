<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2017/4/10
 * Time: 17:11
 */
namespace App\Jobs;

use App\Libraries\TelegramAPI;
use Illuminate\Support\Facades\Log;

class sendUpdateMessageJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $chat_id;
    private $reply_content;
    private $cover_image;
    private $tg_api;
    public function __construct($bot_id, $chat_id, $reply_content, $cover_image)
    {
        //
        $this->chat_id = $chat_id;
        $this->reply_content = $reply_content;
        $this->cover_image = $cover_image;
        $this->tg_api = new TelegramAPI($bot_id);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->cover_image ===  false) {
            $this->tg_api->sendHTMLText($this->chat_id, $this->reply_content);
        } else {
            $this->tg_api->sendPhoto($this->chat_id, $this->reply_content, $this->cover_image);
        }

    }


}