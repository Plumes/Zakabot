<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2018/1/14
 * Time: 16:38
 */

namespace App\Jobs;

use App\Libraries\HTTPUtil;
use Illuminate\Support\Facades\DB;
use Consatan;
use Illuminate\Support\Facades\Log;

class uploadImageJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $post_id;
    private $cdn_id_list;
    private $original_url_list;
    private $post;
    public $timeout = 120;
    public function __construct($post_id,$cdn_id_list,$original_url_list)
    {
        //
        $this->post_id = $post_id;
        $this->cdn_id_list = $cdn_id_list;
        $this->original_url_list = $original_url_list;
        $this->post = DB::table('posts')->where('id',$this->post_id)->first();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        if(empty($this->post)) return;
        $post = $this->post;
        $member = DB::table('idol_members')->where('id',$post->member_id)->first();
        if(empty($member)) return;
        $cover_image = false;
        foreach ($this->cdn_id_list as $k=>$cdn_id) {
            if($cover_image===false) {
                try {
                    $img = HTTPUtil::uploadNGZKImage($post->id, $cdn_id, $this->original_url_list[$k]);
                    if(!empty($img)) {
                        $cover_image = $img['url'];
                        DB::table('posts')->where('id',$post->id)->update([
                            'cover_image' => $cover_image,
                            'cover_image_hash' => null,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::info($e->getMessage());
                }
            } else {
                dispatch((new uploadOtherImages($post->id, $cdn_id, $this->original_url_list[$k]))->delay(1));
            }

        }

        if($cover_image!==false) {
            $reply_content = $member->name." 发表了新的日记\n".$post->title."\n链接: ".$post->url;
        } else {
            $reply_content = $member->name." 发表了新的日记 <b>".$post->title.'</b><br /><a href="'.$post->url.'">查看详情</a>';
        }

        $fans_id_list = DB::table('idol_fans_relation')->where('member_id', $member->id)->pluck('fan_id');
        $fan_list = DB::table('fans')->whereIn('id', $fans_id_list)->get();
        Log::info("notify about ".$member->name." new post:".$post->url);
        $i=0;
        foreach ($fan_list as $fan) {
            dispatch( new sendUpdateMessageJob("309781356", $fan->chat_id, $reply_content, $cover_image) );
        }
    }

    public function failed(\Exception $exception)
    {
        // Send user notification of failure, etc...
        $msg = $exception->getMessage();
        dispatch( new sendUpdateMessageJob("309781356", "307558399", $msg, false) );
    }
}