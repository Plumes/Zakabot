<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2017/4/10
 * Time: 17:11
 */
namespace App\Jobs;

use App\Libraries\TelegramAPI;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class sendEditMessage extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $chat_id;
    private $message_id;
    private $reply_content;
    private $tg_api;
    private $group_id = -1;
    public function __construct($bot_id, $chat_id, $message_id, $reply_content)
    {
        //
        $this->chat_id = $chat_id;
        $this->reply_content = $reply_content;
        $this->message_id = $message_id;
        $this->tg_api = new TelegramAPI($bot_id);
        if($bot_id=="372178022") {
            $this->group_id = 1;
        } elseif ($bot_id=="309781356") {
            $this->group_id = 2;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(env('APP_ENV')=="local") {
            Log::info("send tg:".$this->reply_content);
            return;
        }
        $this->tg_api->editMessage($this->chat_id, $this->message_id, $this->reply_content);
    }


}