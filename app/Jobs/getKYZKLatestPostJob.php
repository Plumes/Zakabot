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

class getKYZKLatestPostJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $official_id;
    public function __construct($official_id=false)
    {
        //
        $this->official_id = $official_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $blog_url = "http://www.keyakizaka46.com/s/k46o/diary/member/list?ima=0000&ct=".sprintf("%02d", $this->official_id);
        $html = file_get_contents($blog_url);
        preg_match('/<article>(.*)<\/article>/uUs', $html, $matches);
        if(empty($matches)) return;
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($matches[0], 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new \DOMXPath($dom);

        $title_node = $xpath->query("//div[@class='innerHead']/div[@class='box-ttl']")->item(0);
        $title = trim($xpath->query('h3/a', $title_node)->item(0)->nodeValue);
        $post_url = $xpath->query('h3/a/@href', $title_node)->item(0)->nodeValue;
        $post_url = "http://www.keyakizaka46.com".$post_url;
        $member_name = trim($xpath->query('p', $title_node)->item(0)->nodeValue);

        if($this->official_id=="1000") {
            $hiragana2_member_names = ['金村美玖','河田陽菜','小坂菜緒','富田鈴花','丹生明里','濱岸ひより','松田好花','宮田愛萌','渡邉美穂'];
            $hiragana2_official_names = ['金村 美玖','河田 陽菜','小坂 菜緒','富田 鈴花','丹生 明里','濱岸 ひより','松田 好花','宮田 愛萌','渡邉 美穂'];
            foreach ($hiragana2_member_names as $k=>$v) {
                if(mb_strpos($title, $v)!==false) {
                    $member = DB::table('idol_members')->where('group_id', 1)->where('name', $hiragana2_official_names[$k])->first();
                    break;
                }
            }
        } else {
            $member = DB::table('idol_members')->where('group_id', 1)->where('official_id', intval($this->official_id))->first();
        }
        if(empty($member)) return;

        $content = $xpath->query("//div[@class='box-article']")->item(0);
        $content_html = $dom->saveXML($content);
        preg_match('/<img.+src="(\S+)"/U', $content_html, $matches);
        $cover_image = false;
        if(isset($matches[1])) {
            $cover_image = $matches[1];
        }
        $post_time = trim($xpath->query("//div[@class='box-bottom']/ul/li")->item(0)->textContent);
        $now = date('Y-m-d H:i:s');
        $post = DB::table('posts')->where('url_hash', md5($post_url))->first();
        if(!empty($post)) return;
        DB::table('posts')->insert([
                'member_id' => $member->id,
                'title' => $title,
                'url' => $post_url,
                'url_hash' => md5($post_url),
                'content' => trim($content_html),
                'cover_image' => $cover_image!==false?$cover_image:'',
                'posted_at' => $post_time,
                'created_at'=>$now,
                'updated_at'=>$now
            ]
        );
        DB::table('idol_members')->where('id',$member->id)->update(['last_post_at'=>$post_time,'updated_at'=>$now]);

        if($post_time<date("Y-m-d H:i:s", time()-3600*48)) return; //如果最新发布时间是两天前就不发送消息了。为了避免故障重启后突发集中推送

        $fans_id_list = DB::table('idol_fans_relation')->where('member_id', $member->id)->pluck('fan_id');
        $fan_list = DB::table('fans')->whereIn('id', $fans_id_list)->get();
        if($cover_image===false) {
            $reply_content = $member_name." 发表了新的日记 <b>".$title.'</b><br /><a href="'.$post_url.'">查看详情</a>';
        } else {
            $reply_content = $member_name." 发表了新的日记\n".$title."\n链接: ".$post_url;
        }

        $i=0;
        foreach ($fan_list as $fan) {
            Log::info('#'.$i);
            dispatch( (new sendUpdateMessageJob("372178022", $fan->chat_id, $reply_content, $cover_image))->delay($i++/10) );
        }
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Send user notification of failure, etc...
        $msg = $exception->getMessage();
        dispatch( new sendUpdateMessageJob("372178022", "307558399", $msg, false) );
    }
}