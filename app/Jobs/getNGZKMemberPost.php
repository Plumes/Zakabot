<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2018/1/17
 * Time: 14:29
 */


namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class getNGZKMemberPost extends Job
{
    private $url;
    private $page_number;
    private $month;
    private $member_id;
    /**
     * Create a new job instance.
     *
     * @return void
     * @param int $member_id
     * @param string $url blog link
     * @param int page_number 1
     * @param int $month 201701
     */
    public function __construct(int $page_number,int $month)
    {
        //
        $this->page_number = $page_number;
        $this->month = $month;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $url = "http://blog.nogizaka46.com/";
        $page_number = $this->page_number;
        $next_page = 0;
        $month = $this->month;
        if($month<201111) return;
        $page_html = file_get_contents($url."?p=".$page_number."&d=".$month);
        $page = new \DOMDocument();
        libxml_use_internal_errors(true);
        $res = $page->loadHTML($page_html, LIBXML_PARSEHUGE);
        if(!$res) return;
        $main_html = $page->getElementById("sheet");
        $xpath = new \DOMXPath($page);
        $paginate = $xpath->query("//div[@class='paginate'][1]/a");
        foreach ($paginate as $v) {
            $pg_number = intval(trim($v->nodeValue, " \t\n\r\0\x0B\xC2\xA0"));
            if($page_number < $pg_number) {
                $next_page = $pg_number;
                break;
            }
        }

        $title_nodes = $xpath->query("//span[@class='entrytitle']/a", $main_html);
        $content_nodes = $xpath->query("//div[@class='entrybody']", $main_html);
        $foot_nodes = $xpath->query("//div[@class='entrybottom']", $main_html);

        if($title_nodes->length != $content_nodes->length || $title_nodes->length!=$foot_nodes->length) {
            Log::error($month." ".$page_number." invalid data");
        } else {
            for($i=0; $i<$title_nodes->length; $i++) {
                $title = $title_nodes->item($i)->nodeValue;
                $url = $title_nodes->item($i)->getAttribute("href");
                preg_match('/com\/(\S+)\/20/', $url, $matches);
                $official_id = $matches[1];
                $member = DB::table('idol_members')->where('official_id',$official_id)->first();
                if(empty($member)) {
                    Log::info("no member:".$url);
                    continue;
                }
                $url_hash = md5($url);
                $content = $page->saveHTML($content_nodes->item($i));
                $preview = trim(strip_tags($content));
                preg_match("/20\d{2}\/\d{2}\/\d{2} \d{2}:\d{2}/", $foot_nodes->item($i)->textContent, $matches);
                $published_at = $matches[0];
                preg_match_all("/http:\/\/[\w,\.\/]+(jpg|jpeg|png)/U", $content, $matches);
                try {
                    $post_id = DB::table('ngzk_posts')->insertGetId([
                            'member_id' => $member->id,
                            'title' => $title,
                            'url' => $url,
                            'url_hash' => $url_hash,
                            'content' => trim($content),
                            'preview' => mb_substr($preview,0,140),
                            'cover_image' => '',
                            'cover_image_hash' => null,
                            'posted_at' => $published_at,
                            'created_at'=>date('Y-m-d H:i:s'),
                            'updated_at'=>date('Y-m-d H:i:s')
                        ]
                    );
                } catch (\Exception $exception) {
                    Log::info($exception->getMessage());
                    continue;
                }

                foreach ($matches[0] as $k=>$v) {
                    dispatch((new uploadImageJob($post_id, null, $v))->delay($k/5));
                }
            }
        }
        if($next_page<1) {
            $next_page = 1;
            $next_month = ($month-1)%100==0?$month-89:$month-1;
        } else {
            $next_month = $month;
        }

        dispatch( new sendUpdateMessageJob("309781356", "307558399", "page:".$this->page_number." month:".$this->month." success", false) );

        dispatch((new getNGZKMemberPost($next_page, $next_month))->delay(30));

        //echo "success next_page:".$next_page." next month:".$next_month;
    }

    public function failed(\Exception $exception)
    {
        // Send user notification of failure, etc...
        $msg = $exception->getMessage();
        dispatch( new sendUpdateMessageJob("309781356", "307558399", "page:".$this->page_number." month:".$this->month." error:".$msg, false) );
    }
}
