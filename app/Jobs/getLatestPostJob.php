<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2017/4/10
 * Time: 10:55
 */
namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class getLatestPostJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $member_id;
    public function __construct($member_id=false)
    {
        //
        $this->member_id = $member_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        if(!$this->member_id) return false;
        $blog_url = "http://www.keyakizaka46.com/s/k46o/diary/member/list?ima=0000&ct=".$this->member_id;
        $html = file_get_contents($blog_url);
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        $article = $xpath->query("//article")[0];
        $title_node = $xpath->query("div[@class='innerHead']/div[@class='box-ttl']", $article)->item(0);
        $title = trim($xpath->query('h3/a', $title_node)->item(0)->nodeValue);
        $post_url = $xpath->query('h3/a/@href', $title_node)->item(0)->nodeValue;
        $member_name = trim($xpath->query('p', $title_node)->item(0)->nodeValue);
        $content = $xpath->query("div[@class='box-article']", $article)->item(0);
        $content_html = $dom->saveXML($content);
        $content_html = preg_replace('/<div .*>/', '', $content_html);
        $content_html = preg_replace('/<\/div>/', '\n', $content_html);
        $content_html = preg_replace('/<br\/>/', '\n', $content_html);
        preg_match('/<img src="(\S+)"\s*\/>/', $content_html, $matches);
        $cover_image = false;
        if(isset($matches[1])) {
            $cover_image = $matches[1];
        }
        $post_time = trim($xpath->query("div[@class='box-bottom']/ul/li", $article)->item(0)->textContent);
        $now = date('Y-m-d H:i:s');
        DB::table('posts')->insert([
                'member_id' => intval($this->member_id),
                'title' => $title,
                'url' => 'http://www.keyakizaka46.com'.$post_url,
                'content' => trim($content_html),
                'cover_image' => $cover_image!==false?$cover_image:'',
                'posted_at' => $post_time,
                'created_at'=>$now,
                'updated_at'=>$now
            ]
        );
        DB::table('kyzk46_members')->where('id',intval($this->member_id))->update(['last_post_at'=>$post_time,'updated_at'=>$now]);

        $fans_chat_list = DB::table('idol_fans_relation')->where('member_id', intval($this->member_id))->get();
        if($cover_image===false) {
            $reply = $member_name." 发表了新的日记 <b>".$title.'</b><a href="'.$post_url.'">查看详情</a>';
        } else {
            $reply = $member_name."发表了新的日记 ".$title." 链接: ".$post_url;
        }

        $i=0;
        foreach ($fans_chat_list as $chat) {
            Log::info('#'.$i);
            dispatch((new sendUpdateMessageJob($chat->chat_id, $reply, $cover_image))->delay($i++/10));
        }
        return true;
    }
}