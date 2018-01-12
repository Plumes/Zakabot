<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2017/4/10
 * Time: 14:59
 */

namespace App\Http\Controllers;

use App\Jobs\getKYZKLatestPostJob;
use App\Jobs\sendUpdateMessageJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Libraries\HTTPUtil;
use phpDocumentor\Reflection\Types\Null_;

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
        $posts = DB::table('posts')
            ->join('idol_members','posts.member_id','=','idol_members.id')
            ->select('posts.*','idol_members.name','idol_members.profile_pic')
            ->where('idol_members.group_id',2)
            ->orderBy('posts.id','desc')
            ->limit(10)
            ->get();
        foreach ($posts as $post) {
            $desc = trim(strip_tags($post->content));
            $post->content = mb_substr($desc,0,140)."......";
            $post->inner_url = url("/amp/nogizaka46/".$post->member_id."/".$post->id);
            if(empty($post->profile_pic)) {
                $post->profile_pic = url("/images/nogizaka46_logo.jpg");
            }
        }
        $schema_meta = [
            "@context"=>"http://schema.org",
            "@type"=>"BlogPosting",
            "mainEntityOfPage"=>"http://blog.nogizaka46.com/",
            "headline"=>"乃木坂46 公式ブログ",
            "datePublished"=>date('c', time()),
            'author'=>["@type"=>"Person",'name'=>"乃木坂46"],
            "publisher"=>[
                "@type"=>"Organization",
                'name'=>"乃木坂46",
                "legalName"=>"Nogizaka46",
                "logo"=>[
                    "@type"=>"ImageObject",
                    "url"=>url("/images/nogizaka46_logo.jpg"),
                    "width"=>400,
                    "height"=>400
                ],
                "description"=>"乃木坂46 公式ブログ"
            ]
        ];
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
        $content = file_get_contents("http://blog.nogizaka46.com/third/2017/12/042080.php");
        preg_match('/<a href="http:\/\/dcimg\.awalker\.jp\/img1\.php\?id=(\w+)".*<img/sU', $content, $matches);
        if(isset($matches[1])) {
            $img_url = "http://dcimg.awalker.jp/img1.php?id=".$matches[1];
            $url_hash = md5($img_url);
            HTTPUtil::get($img_url, $url_hash);
            $img_url = "http://dcimg.awalker.jp/img2.php?sec_key=".$matches[1];
            $img_file = HTTPUtil::get($img_url, $url_hash);
            if($img_file!=false) {
                file_put_contents("/tmp/".$url_hash.".jpg", $img_file);
                $result = HTTPUtil::post("https://api.telegram.org/bot309781356:AAFl5KmawS2-x56V8jv-c4t43pjnPFRLPMs/sendPhoto", [
                    'chat_id'=>"307558399",
                    'photo'=>curl_file_create("/tmp/".$url_hash.".jpg")
                ]);
                if($result!==false) {
                    $result = json_decode($result, true);
                    if(is_array($result['result']['photo'])) {
                        $size = count($result['result']['photo']);
                        $cover_image = $result['result']['photo'][$size-1]['file_id'];
                        $cover_image_hash = null;
                    }
                }
            }
            if(file_exists("/tmp/".$url_hash)) unlink("/tmp/".$url_hash);
            if(file_exists("/tmp/".$url_hash.".jpg")) unlink("/tmp/".$url_hash.".jpg");
            dispatch( (new sendUpdateMessageJob("309781356", "307558399", "test", $cover_image))->delay(1) );

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
        $post->content = str_replace('<font size="1">', '<div class="font-size-1">', $post->content);
        $post->content = str_replace('</font>', '</div>', $post->content);
        $replace_pattern = '<a$1><div class="fixed-height-container"><amp-img class="contain" layout="fill" src="$2"></amp-img></div></a>';
        $post->content = preg_replace("/<a(.*)><img.+src=\"([\w,:,\/,\.]+)\".*\/><\/a>/U", $replace_pattern, $post->content);
        if(mb_strlen($post->title)>20) {
            $post->abbr_title = (mb_substr($post->title,0,20))."...";
        } else {
            $post->abbr_title = $post->title;
        }
        if(empty($member->profile_pic)) {
            $member->profile_pic = url("/images/nogizaka46_logo.jpg");
        }
        $post->prev = DB::table('posts')->where('member_id', $post->member_id)->where('id','<',$post->id)->orderBy('id','desc')->value('id');
        $post->next = DB::table('posts')->where('member_id', $post->member_id)->where('id','>',$post->id)->orderBy('id','asc')->value('id');
        $desc = trim(strip_tags($post->content));

        $schema_meta = [
            "@context"=>"http://schema.org",
            "@type"=>"BlogPosting",
            "mainEntityOfPage"=>$post->url,
            "headline"=>"乃木坂46 ".$member->name." ".$post->title,
            "datePublished"=>str_replace(' ','T',$post->posted_at)."+09:00",
            'author'=>["@type"=>"Person",'name'=>$member->name],
            "publisher"=>[
                "@type"=>"Organization",
                'name'=>"乃木坂46",
                "legalName"=>"Nogizaka46",
                "logo"=>[
                    "@type"=>"ImageObject",
                    "url"=>url("/images/nogizaka46_logo.jpg"),
                    "width"=>400,
                    "height"=>400
                ],
                "description"=>mb_substr($desc,0,40)
            ]
        ];
        return view('amp_post',['post'=>$post,'member'=>$member,"schema"=>$schema_meta]);
    }

}