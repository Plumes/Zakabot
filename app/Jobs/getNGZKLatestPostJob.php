<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2017/5/8
 * Time: 16:26
 */
namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Libraries\HTTPUtil;
use Exception;

class getNGZKLatestPostJob extends Job
{
    public $timeout = 150;
    private $article_html;
    public function __construct($article_html)
    {
        //
        $this->article_html = $article_html;
    }

    public function handle() {
        $article = simplexml_load_string($this->article_html);
        if(!($article instanceof \SimpleXMLElement)) return;
        $title = (string)$article->title;
        $post_url =(string)$article->link->attributes()->href;
        $published_at = (string)$article->published;
        $published_at = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $published_at);
        $published_at->setTimeZone(new \DateTimeZone('Asia/Tokyo'));
        //防止执行重复的任务
        $post_url_hash = md5($post_url);
        $check_post = DB::table('ngzk_posts')->where('url_hash', $post_url_hash)->first();
        if(!empty($check_post)) return;

        preg_match('/com\/(\S+)\/20/', $post_url, $matches);
        $official_id = $matches[1];
        $member = null;
        if($official_id=="third") {
            $t_title = str_replace(' ','', $title);
            $third_member_names = ['伊藤理々杏','岩本蓮加','梅澤美波','大園桃子','久保史緒里','阪口珠美','佐藤楓','中村麗乃','向井葉月','山下美月','吉田綾乃クリスティー','与田祐希'];
            foreach ($third_member_names as $v) {
                if(mb_strpos($t_title, $v)!==false) {
                    $v = str_replace(' ', '', $v);
                    $member = DB::table('idol_members')->where('group_id', 2)->where('name', $v)->first();
                    break;
                }
            }
        } else {
            $member = DB::table('idol_members')->where('group_id', 2)->where('official_id', $official_id)->first();
        }
        if(empty($member)) return;

        $content_html = (string)$article->content;
        $preview = trim(strip_tags($content_html));

        DB::table('idol_members')->where('id', $member->id)->update([
            'last_post_at'=>$published_at->format('Y-m-d H:i:s'),
            'updated_at'=>date('Y-m-d H:i:s')
        ]);

        $cover_image = false;
        $cover_image_hash = null;
        preg_match('/<img[^>]src="(\S+)"/U', $content_html, $matches);
        if(isset($matches[1])) {
            $cover_image = $matches[1];
        }


        $post_id = DB::table('ngzk_posts')->insertGetId([
                'member_id' => $member->id,
                'title' => $title,
                'preview' => trim(mb_substr($preview, 0, 140)),
                'url' => $post_url,
                'url_hash' => $post_url_hash,
                'content' => trim($content_html),
                'cover_image' => $cover_image!==false?$cover_image:'',
                'cover_image_hash' => $cover_image_hash,
                'posted_at' => $published_at->format('Y-m-d H:i:s'),
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>date('Y-m-d H:i:s')
            ]
        );

        HTTPUtil::submitMIP(['https://zakabot.zhh.me/amp/nogizaka46/'.$member->id."/".$post_id]);

        preg_match_all('/<a href="http:\/\/dcimg\.awalker\.jp\/img1\.php\?id=(\w+)"><img.+src="([\w,:,\/,\.]+)"><\/a>/U', $content_html, $matches);
        if(count($matches[0])>0) {
            try {
                $img_url = "http://dcimg.awalker.jp/img1.php?id=" . $matches[1][0];
                $url_hash = md5($img_url);
                HTTPUtil::get($img_url, $url_hash);
                $img_url = "http://dcimg.awalker.jp/img2.php?sec_key=" . $matches[1][0];
                $img_file = HTTPUtil::get($img_url, $url_hash);
                if ($img_file != false) {
                    file_put_contents("/tmp/" . $url_hash . ".jpg", $img_file);
                    $result = HTTPUtil::post("https://api.telegram.org/bot309781356:AAFl5KmawS2-x56V8jv-c4t43pjnPFRLPMs/sendPhoto", [
                        'chat_id' => "307558399",
                        'photo' => curl_file_create("/tmp/" . $url_hash . ".jpg")
                    ]);
                    if ($result !== false) {
                        $result = json_decode($result, true);
                        if (is_array($result['result']['photo'])) {
                            $size = count($result['result']['photo']);
                            $cover_image = $result['result']['photo'][$size - 1]['file_id'];
                            $cover_image_hash = null;
                        }
                    }
                }
                if (file_exists("/tmp/" . $url_hash)) unlink("/tmp/" . $url_hash);
                if (file_exists("/tmp/" . $url_hash . ".jpg")) unlink("/tmp/" . $url_hash . ".jpg");
            } catch (\Exception $exception) {
                Log::info($exception->getMessage());
            }

            foreach ($matches[1] as $k=>$cdn_id) {
                dispatch((new uploadImageJob($post_id, $cdn_id, $matches[2][$k]))->delay(1));
            }

        }

        //去除外部链接
        $search_pattern = "/<a[^>]*>(<img.+><\/img>)<\/a>/U";
        $content = preg_replace($search_pattern,"$1",$content_html);
        DB::table('ngzk_posts')->where('id',$post_id)->update(['content'=>$content]);


        $fans_id_list = DB::table('idol_fans_relation')->where('member_id', $member->id)->pluck('fan_id');
        $fan_list = DB::table('fans')->whereIn('id', $fans_id_list)->get();
        if($cover_image===false) {
            $reply_content = $member->name." 发表了新的日记 <b>".mb_substr($title,0,70).'</b><br /><a href="'.$post_url.'">查看详情</a>';
        } else {
            $reply_content = $member->name." 发表了新的日记\n".mb_substr($title,0,70)."\n链接: ".$post_url;
        }
        Log::info("notify about ".$member->name." new post:".$post_url);
        $i=0;
        foreach ($fan_list as $fan) {
            dispatch( (new sendUpdateMessageJob("309781356", $fan->chat_id, $reply_content, $cover_image))->delay($i++/10) );
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
        dispatch( new sendUpdateMessageJob("309781356", "307558399", $msg, false) );
    }
}