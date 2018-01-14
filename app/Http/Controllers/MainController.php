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
use App\Jobs\uploadImageJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Libraries\HTTPUtil;
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
        $blog_url = "http://blog.nogizaka46.com/atom.xml";
        $html = file_get_contents($blog_url);
        $xml = simplexml_load_string($html);

        if(count($xml->entry)<1) return;
        $i=0;
        foreach ($xml->entry as $article) {
            $post_url = (string)$article->link->attributes()->href;
            if(empty($post_url)) continue;
            $post_url_hash = md5($post_url);
            $content = (string)$article->content;
            preg_match_all('/<a href="http:\/\/dcimg\.awalker\.jp\/img1\.php\?id=(\w+)".*><img.+src="([\w,:,\/,\.]+)".*><\/a>/U', $content, $matches);
            foreach ($matches[0] as $k=>$v) {
                dispatch(new uploadImageJob(1,$matches[1][$k], $matches[2][$k]));
            }
            $res = DB::table('post_images')->count();
            var_dump($res);
            exit;

//            $post = DB::table('posts')->where('url_hash', $post_url_hash)->first();
//            if(!empty($post)) continue;
//            $delay = $i++*5+1;
//            Log::info("new post:".$post_url." appointment at ".Date("m-d H:i:s", time()+$delay));
//            dispatch( (new getNGZKLatestPostJob($article->asXML()))->delay($delay) );
        }
//        $content = file_get_contents("http://blog.nogizaka46.com/third/2018/01/042849.php");
//        preg_match('/<a href="http:\/\/dcimg\.awalker\.jp\/img1\.php\?id=(\w+)".*<img/sU', $content, $matches);
//        if(isset($matches[1])) {
//            $img_url = "http://dcimg.awalker.jp/img1.php?id=".$matches[1];
//            $url_hash = md5($img_url);
//            HTTPUtil::get($img_url, $url_hash);
//            $img_url = "http://dcimg.awalker.jp/img2.php?sec_key=".$matches[1];
//            $img_file = HTTPUtil::get($img_url, $url_hash);
//            if($img_file!=false) {
//                file_put_contents("/tmp/".$url_hash.".jpg", $img_file);
//                $weibo = new Consatan\Weibo\ImageUploader\Client();
//
//// 默认返回的是 https 协议的图床 URL，调用该方法返回的是 http 协议的图床 URL
//// $weibo->useHttps(false);
//
//// 上传示例图片
//                $url = $weibo->upload("/tmp/".$url_hash.".jpg", 'prctrash@126.com', '0okmnji9');
//
//// 输出新浪图床 URL
//                echo $url . PHP_EOL;
//            }
//            if(file_exists("/tmp/".$url_hash)) unlink("/tmp/".$url_hash);
//            if(file_exists("/tmp/".$url_hash.".jpg")) unlink("/tmp/".$url_hash.".jpg");
//
//        }
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
        }
        return view('amp_post',['post'=>$post,'member'=>$member,"schema"=>$schema_meta]);
    }

}