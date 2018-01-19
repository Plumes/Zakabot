<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2017/4/10
 * Time: 14:59
 */

namespace App\Http\Controllers;

use App\Jobs\getKYZKLatestPostJob;
use App\Jobs\getNGZKLatestPostJob;
use App\Jobs\getNGZKMemberPost;
use App\Jobs\sendUpdateMessageJob;
use App\Jobs\uploadImageJob;
use App\Libraries\TelegramAPI;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Libraries\HTTPUtil;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Null_;
use Consatan;

class MainController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function post_list() {
        $schema_meta = [
            "@context"=>"http://schema.org",
            "@type"=>"BlogPosting",
            "mainEntityOfPage"=>"http://blog.nogizaka46.com/",
            "headline"=>"乃木坂46 公式ブログ",
            "datePublished"=>'',
            'author'=>["@type"=>"Person",'name'=>"乃木坂46"],
            "publisher"=>[
                "@type"=>"Organization",
                'name'=>"乃木坂46",
                "legalName"=>"Nogizaka46",
                "logo"=>[
                    "@type"=>"ImageObject",
                    "url"=>url("/images/nogizaka46_amp_logo.png"),
                    "width"=>600,
                    "height"=>60
                ],
                "description"=>"乃木坂46 公式ブログ"
            ],
            "dateModified"=>'',
        ];
        $posts = DB::table('posts')
            ->join('idol_members','posts.member_id','=','idol_members.id')
            ->select('posts.*','idol_members.name','idol_members.profile_pic')
            ->where('idol_members.group_id',2)
            ->orderBy('posts.posted_at','desc')
            ->limit(10)
            ->get();
        foreach ($posts as $post) {
            $desc = trim(strip_tags($post->content));
            $post->content = mb_substr($desc,0,140)."......";
            $post->inner_url = url("/amp/nogizaka46/".$post->member_id."/".$post->id);
            if(empty($post->profile_pic)) {
                $post->profile_pic = url("/images/nogizaka46_logo.jpg");
            }

            $UTC = new \DateTimeZone("UTC");
            $newTZ = new \DateTimeZone("Asia/Tokyo");
            $date = new \DateTime( $post->posted_at, $UTC );
            $date->setTimezone( $newTZ );
            $post->posted_at = $date->format('Y-m-d H:i:s');
            if(empty($schema_meta['datePublished'])) {
                $schema_meta['datePublished'] = $schema_meta['dateModified'] = $date->format('Y-m-dTH:i:s+09:00');
            }
            if(!isset($schema_meta['image'])) {
                $img = DB::table('post_images')->where('post_id',$post->id)->first();
                if(!empty($img)) {
                    $schema_meta['image'] = [
                        "@type"=>"ImageObject",
                        "url"=>$img->url,
                        "width"=>$img->width,
                        "height"=>$img->height
                    ];
                }

            }
        }
        return view('ngzk_index',['posts'=>$posts,'logo'=>url("/images/nogizaka46_logo.jpg"),"schema"=>$schema_meta]);
    }

    //
    public function crawl() {
        $result = DB::table("idol_fans_relation")
            ->select(DB::raw("DISTINCT(fan_id) as fan_id"))
            ->where('member_id','<',34)
            ->get();
        $fan_id_list = [];
        foreach($result as $v) {
            $fan_id_list[] = $v->fan_id;
        }
        $kyzk_fans = DB::table('fans')
            ->select('chat_id')
            ->whereIn('id', $fan_id_list)
            ->get();
        $reply_content = "非常抱歉，在官网更新加入平假名二期数据后，没有及时更新，导致故障了一段时间，现在已经修复，并加入了假名二期支持，欢迎订阅";
        foreach ($kyzk_fans as $fan) {
            dispatch( (new sendUpdateMessageJob("372178022", $fan->chat_id, $reply_content, false)) );
        }
    }
    public function test() {
        //dispatch((new getNGZKMemberPost(42,201704, 0))->delay(1));
        echo "start";
        $url = "http://blog.nogizaka46.com/";
        $page_number = 12;
        $next_page = 0;
        $month = 201512;
        if($month<201111) return;
        $page_html = file_get_contents($url."?p=".$page_number."&d=".$month);
        $page = new \DOMDocument();
        libxml_use_internal_errors(true);
        $res = $page->loadHTML($page_html, LIBXML_PARSEHUGE | LIBXML_HTML_NOIMPLIED);
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
            for ($i = 0; $i < $title_nodes->length; $i++) {
                $title = $title_nodes->item($i)->nodeValue;
                $url = $title_nodes->item($i)->getAttribute("href");
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
            }
        }
    }

    public function generateAMP_NGZK($member_id, $post_id) {
        $member = DB::table('idol_members')->where('id', $member_id)->where('group_id', 2)->first();
        if(empty($member)) return response('404');
        $post = DB::table('posts')->where('id',$post_id)->where('member_id',$member->id)->first();
        if(empty($post) || $post->member_id != $member_id) return response('404');

        $post->content = preg_replace("/<content.*>/U",'', $post->content);
        $post->content = str_replace("]]&gt;", '', $post->content);
        $post->content = str_replace("</content>", '', $post->content);
        $post->content = str_replace('<div> </div>', '<p></p>', $post->content);
        $post->content = preg_replace("/<div>(<font size=\"1\">)+<br\/>(<\/font>)+<\/div>/", "<p></p>", $post->content);
        //$post->content = str_replace('<font size="1">', '<div class="font-size-1">', $post->content);
        //$post->content = str_replace('</font>', '</div>', $post->content);

        $uploaded_images = DB::table('post_images')->where('post_id', $post_id)->get();
        foreach ($uploaded_images as $img) {
            $replace_pattern = "<amp-img src=\"$img->url\" width=\"$img->width\" height=\"$img->height\" layout=\"responsive\"></amp-img>";
            $img->original_url = str_replace(['/','.'],['\/','\.'], $img->original_url);
            $search_pattern = "/<a href=[^>]+><img[^>]+src=\"$img->original_url\"[^>]*><\/a>/U";
            $post->content = preg_replace($search_pattern,$replace_pattern,$post->content);
        }

        $replace_pattern = '<div class="fixed-height-container"><a$1><amp-img class="contain" layout="fill" src="$2"></amp-img></a></div>';
        $post->content = preg_replace("/<a([^>]+)><img.+src=\"([\w,:,\/,\.]+)\".*><\/a>/U", $replace_pattern, $post->content);
        $replace_pattern = '<div class="fixed-height-container"><amp-img class="contain" layout="fill" src="$1"></amp-img></div>';
        $post->content = preg_replace("/<img.+src=\"([\w,:,\/,\.]+)\".*>/U", $replace_pattern, $post->content);

        $post->content = preg_replace("/href=\"(x-apple-data-detectors:\/\/\w+)\"/U",'href=""', $post->content);
        $post->content = preg_replace("/x-apple-data.+=\".+\"/U",'', $post->content);
        $post->content = preg_replace("/<blockquote[^>]+type=[^>]+>/U",'<blockquote>', $post->content);
        if(mb_strlen($post->title)>20) {
            $post->abbr_title = (mb_substr($post->title,0,20))."...";
        } else {
            $post->abbr_title = $post->title;
        }
        if(empty($member->profile_pic)) {
            $member->profile_pic = url("/images/nogizaka46_logo.png");
        }
        $post->prev = DB::table('posts')->where('member_id', $post->member_id)->where('id','<',$post->id)->orderBy('id','desc')->value('id');
        $post->next = DB::table('posts')->where('member_id', $post->member_id)->where('id','>',$post->id)->orderBy('id','asc')->value('id');
        $desc = trim(strip_tags($post->content));

        $UTC = new \DateTimeZone("UTC");
        $newTZ = new \DateTimeZone("Asia/Tokyo");
        $date = new \DateTime( $post->posted_at, $UTC );
        $date->setTimezone( $newTZ );
        $post->posted_at = $date->format('Y-m-d H:i:s');

        $schema_meta = [
            "@context"=>"http://schema.org",
            "@type"=>"BlogPosting",
            "mainEntityOfPage"=>url("/amp/nogizaka46/".$member->id."/".$post->id),
            "headline"=>"乃木坂46 ".$member->name." ".$post->title." ".mb_substr($desc,0,30),
            "datePublished"=>str_replace(' ','T',$post->posted_at)."+09:00",
            "dateModified"=>str_replace(' ','T',$post->posted_at)."+09:00",
            'author'=>["@type"=>"Person",'name'=>$member->name],
            "publisher"=>[
                "@type"=>"Organization",
                'name'=>"乃木坂46",
                "legalName"=>"Nogizaka46",
                "logo"=>[
                    "@type"=>"ImageObject",
                    "url"=>url("/images/nogizaka46_amp_logo.png"),
                    "width"=>600,
                    "height"=>60
                ],
                "description"=>"乃木坂46 公式ブログ Official Blog"
            ]
        ];

        $img = DB::table('post_images')->where('post_id',$post->id)->first();
        if(!empty($img)) {
            $schema_meta['image'] = [
                "@type"=>"ImageObject",
                "url"=>$img->url,
                "width"=>$img->width,
                "height"=>$img->height
            ];
        } elseif (!empty($post->cover_image) && substr($post->cover_image, 0, 4)=="http") {
            $schema_meta['image'] = [
                "@type"=>"ImageObject",
                "url"=>$post->cover_image,
                "width"=>240,
                "height"=>0
            ];
        }
        return view('amp_post',['post'=>$post,'member'=>$member,"schema"=>$schema_meta]);
    }

    public function sendTestMsg() {
        $api_base = "https://api.telegram.org/bot309781356:AAFl5KmawS2-x56V8jv-c4t43pjnPFRLPMs/";
        $api_url = $api_base."sendMessage";
        $post_data = [
            'chat_id' => "307558399",
            'text' => "test",
            'parse_mode' => 'HTML'
        ];
        list($return_code, $return_content) = HTTPUtil::post_json($api_url, json_encode($post_data));
        return $return_content;
    }

}