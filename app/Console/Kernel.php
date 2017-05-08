<?php

namespace App\Console;

use App\Jobs\getKYZKLatestPostJob;
use App\Jobs\getNGZKLatestPostJob;
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
        //keyakizaka46 task
        $schedule->call(function () {
            $file_content = "";
            $content = file_get_contents("http://www.keyakizaka46.com/s/k46o/diary/member/list?ima=0000");
            preg_match("/blogUpdate = (\[.*\])/s", $content, $matches);
            $result = $matches[1];
            $result = preg_replace("/\n/s", "", $result);
            $result = preg_replace("/member/", "\"member\"", $result);
            $result = preg_replace("/update/", "\"update\"", $result);
            $result = json_decode($result, true);
            $member_list = DB::table('idol_members')->where('group_id',1)->get();
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
                    Log::info("craw kyzk ".$v['member']." current:".$current_update_at." last: ".$member_last_post_list[intval($v['member'])]."\n");
                    dispatch( (new getKYZKLatestPostJob($v['member']))->delay(5*$i++) );
                }

            }
        })->everyTenMinutes();

        //nogizaka46 task
        $schedule->call(function () {
            $blog_url = "http://blog.nogizaka46.com/atom.xml";
            $html = file_get_contents($blog_url);
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($html);
            $xpath = new \DOMXPath($dom);

            $article_list = $xpath->query("//entry");
            $i=0;
            foreach ($article_list as $article) {
                $title = $xpath->query("title", $article)->item(0)->nodeValue;
                $post_url = $xpath->query('link/@href', $article)->item(0)->nodeValue;
                $post_url_hash = md5($post_url);

                $post = DB::table('posts')->where('url_hash', $post_url_hash)->first();
                if(!empty($post)) continue;
                $delay = $i++*5+1;
                $article_html = $dom->saveXML($article);
                dispatch( (new getNGZKLatestPostJob($article_html))->delay($delay) );

            }
        })->hourlyAt(30);
    }
}
