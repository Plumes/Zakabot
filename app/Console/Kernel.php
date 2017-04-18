<?php

namespace App\Console;

use App\Jobs\getKYZKLatestPostJob;
use App\Jobs\sendUpdateMessageJob;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $file_content = "";
            $content = file_get_contents("http://www.keyakizaka46.com/s/k46o/diary/member/list?ima=0000");
            preg_match("/blogUpdate = (\[.*\])/s", $content, $matches);
            $result = $matches[1];
            $result = preg_replace("/\n/s", "", $result);
            $result = preg_replace("/member/", "\"member\"", $result);
            $result = preg_replace("/update/", "\"update\"", $result);
            $result = json_decode($result, true);
            $member_list = DB::table('idol_members')->get();
            $member_last_post_list = [];
            foreach ($member_list as $member) {
                $member_last_post_list[intval($member->official_id)] = $member->last_post_at;
            }
            $i=0;
            foreach ($result as $v) {
                preg_match('/(.*)\+/', $v['update'], $matches);
                $current_update_at = $matches[1];
                $current_update_at = preg_replace('/T/',' ', $current_update_at);

                if(isset($member_last_post_list[intval($v['member'])]) && $current_update_at.":00">$member_last_post_list[intval($v['member'])]) {
                    dispatch( (new getKYZKLatestPostJob($v['member']))->delay(5*$i++) );
                }

            }
        })->hourly();

        $schedule->call(function () {
            $blog_url = "http://blog.nogizaka46.com/atom.xml";
            $html = file_get_contents($blog_url);
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($html);
            $xpath = new \DOMXPath($dom);

            $article_list = $xpath->query("//entry");
            foreach ($article_list as $article) {
                $title = $xpath->query("title", $article)->item(0)->nodeValue;
                $post_url = $xpath->query('link/@href', $article)->item(0)->nodeValue;
                $post_url_hash = md5($post_url);

                $post = DB::table('posts')->where('url_hash', $post_url_hash)->first();
                if(!empty($post)) continue;

                preg_match('/com\/(\S+)\/20/', $post_url, $matches);
                $official_id = $matches[1];
                if($official_id=="third") continue;

                $published_at = $xpath->query('published', $article)->item(0)->nodeValue;
                $published_at = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $published_at);
                $published_at->setTimeZone(new \DateTimeZone('Asia/Tokyo'));

                $member_name = $xpath->query('author/name', $article)->item(0)->nodeValue;

                $content = $xpath->query('content', $article)->item(0);
                $content_html = $dom->saveXML($content);

                preg_match('/<img.+src="(\S+)"/U', $content_html, $matches);
                $cover_image = false;
                if(isset($matches[1])) {
                    $cover_image = $matches[1];
                }

                $member = DB::table('idol_members')->where('group_id', 2)->where('official_id', $official_id)->first();
                if(empty($member)) {
                    $member_id = DB::table('idol_members')->insertGetId([
                        'group_id'=>2,
                        'official_id'=>$official_id,
                        'name'=>$member_name,
                        'last_post_at'=>$published_at->format('Y-m-d H:i:s'),
                        'created_at'=>date('Y-m-d H:i:s'),
                        'updated_at'=>date('Y-m-d H:i:s')
                    ]);
                } else {
                    DB::table('idol_members')->where('id', $member->id)->update([
                        'last_post_at'=>$published_at->format('Y-m-d H:i:s'),
                        'updated_at'=>date('Y-m-d H:i:s')
                    ]);
                    $member_id = $member->id;
                }

                DB::table('posts')->insert([
                        'member_id' => $member_id,
                        'title' => $title,
                        'url' => $post_url,
                        'url_hash' => $post_url_hash,
                        'content' => trim($content_html),
                        'cover_image' => $cover_image!==false?$cover_image:'',
                        'posted_at' => $published_at->format('Y-m-d H:i:s'),
                        'created_at'=>date('Y-m-d H:i:s'),
                        'updated_at'=>date('Y-m-d H:i:s')
                    ]
                );

                $fans_id_list = DB::table('idol_fans_relation')->where('member_id', $member_id)->pluck('fan_id');
                $fan_list = DB::table('fans')->whereIn('id', $fans_id_list)->get();
                if($cover_image===false) {
                    $reply_content = $member_name." 发表了新的日记 <b>".$title.'</b><br /><a href="'.$post_url.'">查看详情</a>';
                } else {
                    $reply_content = $member_name." 发表了新的日记\n".$title."\n链接: ".$post_url;
                }

                $i=0;
                foreach ($fan_list as $fan) {
                    Log::info('#'.$i);
                    dispatch( (new sendUpdateMessageJob("309781356", $fan->chat_id, $reply_content, $cover_image))->delay($i++/10) );
                }

            }
        })->hourlyAt(30);
    }
}
