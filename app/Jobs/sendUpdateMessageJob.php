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
    private $group_id = -1;
    public function __construct($bot_id, $chat_id, $reply_content, $cover_image)
    {
        //
        $this->chat_id = $chat_id;
        $this->reply_content = $reply_content;
        $this->cover_image = $cover_image;
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
        if($this->cover_image ===  false) {
            $result = $this->tg_api->sendHTMLText($this->chat_id, $this->reply_content);
        } else {
            $result =  $this->tg_api->sendPhoto($this->chat_id, $this->reply_content, $this->cover_image);
        }
        if($result!==true) {
            try{
                $result = json_encode($result, true);
                if(isset($result['error_code']) && $result['error_code']=="403") {
                    $fan = DB::table('fans')->where('chat_id',$this->chat_id)->find();
                    if(empty($fan)) return;
                    $member_id_list = DB::table('idol_members')->where('group_id',$this->group_id)->pluck('id');
                    DB::table('idol_fans_relation')->where('fan_id',$fan->id)->whereIn('member_id', $member_id_list)->delete();
                }
            } catch (\Exception $exception) {
                Log::info($exception->getMessage());
            }
        }

    }


}