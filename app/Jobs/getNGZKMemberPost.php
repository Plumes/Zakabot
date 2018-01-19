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
    private $total_number;
    /**
     * Create a new job instance.
     *
     * @return void
     * @param int $page_number
     * @param int $month 201701
     * @param int $total_number 1
     */
    public function __construct(int $page_number,int $month, int $total_number)
    {
        //
        $this->page_number = $page_number;
        $this->month = $month;
        $this->total_number = $total_number;
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
        $this->total_number += $title_nodes->length;
        $dealt = 0;
        if($title_nodes->length != $content_nodes->length || $title_nodes->length!=$foot_nodes->length) {
            Log::error($month." ".$page_number." invalid data");
            dispatch( new sendUpdateMessageJob("309781356", "307558399", "page:".$this->page_number." month:".$this->month." invalid", false) );
        } else {
            for($i=0; $i<$title_nodes->length; $i++) {
                $title = $title_nodes->item($i)->nodeValue;
                $url = $title_nodes->item($i)->getAttribute("href");
                $test_post = DB::table('ngzk_posts')->where('url_hash', md5($url))->first();
                if(empty($test_post)) {
                    preg_match('/com\/(\S+)\/20/', $url, $matches);
                    $official_id = $matches[1];
                    $member = null;
                    if ($official_id == "third" || $official_id == "kenkyusei") {
                        $member_names = [];
                        $member_names['third'] = ['伊藤理々杏', '岩本蓮加', '梅澤美波', '大園桃子', '久保史緒里', '阪口珠美', '佐藤楓', '佐藤 楓', '中村麗乃', '向井葉月', '山下美月', '吉田綾乃クリスティー', '与田祐希'];
                        $member_names['kenkyusei'] = ['渡辺みり愛', '新内眞衣', '北野日奈子', '堀未央奈', '伊藤かりん', '寺田蘭世', '佐々木琴子', '山﨑怜奈', '伊藤純奈', '鈴木絢音'];
                        foreach ($member_names[$official_id] as $v) {
                            if (mb_strpos($title, $v) !== false) {
                                $v = str_replace(' ', '', $v);
                                $member = DB::table('idol_members')->where('group_id', 2)->where('name', $v)->first();
                                break;
                            }
                        }
                    } else {
                        $member = DB::table('idol_members')->where('official_id', $official_id)->first();
                    }
                    if (empty($member)) {
                        Log::info("no member:" . $url);
                        continue;
                    }
                    $url_hash = md5($url);
                    $content = $page->saveHTML($content_nodes->item($i));
                    $preview = trim(strip_tags($content));
                    preg_match("/20\d{2}\/\d{2}\/\d{2} \d{2}:\d{2}/", $foot_nodes->item($i)->textContent, $matches);
                    $published_at = $matches[0];
                    try {
                        $post_id = DB::table('ngzk_posts')->insertGetId([
                                'member_id' => $member->id,
                                'title' => $title,
                                'url' => $url,
                                'url_hash' => $url_hash,
                                'content' => trim($content),
                                'preview' => mb_substr($preview, 0, 140),
                                'cover_image' => '',
                                'cover_image_hash' => null,
                                'posted_at' => $published_at,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]
                        );
                    } catch (\Exception $exception) {
                        Log::info($exception->getMessage());
                        continue;
                    }
                } elseif ($month>201501 && $month<201513) {
                    $post_id = $test_post->id;
                    $content = $test_post->content;
                } else {
                    continue;
                }

                $dealt++;
                preg_match_all("/http:\/\/[\w,\.\/]+(jpg|jpeg|png)/U", $content, $matches);
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

        dispatch( new sendEditMessage("309781356", "307558399", "","page:".$this->page_number." month:".$this->month." success ".$this->total_number) );

        dispatch((new getNGZKMemberPost($next_page, $next_month, $this->total_number))->delay($dealt*5));

        //echo "success next_page:".$next_page." next month:".$next_month;
    }

    public function failed(\Exception $exception)
    {
        // Send user notification of failure, etc...
        $msg = $exception->getMessage();
        dispatch( new sendUpdateMessageJob("309781356", "307558399", "page:".$this->page_number." month:".$this->month." error:".$msg, false) );
    }
}
