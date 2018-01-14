<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2018/1/14
 * Time: 16:38
 */

namespace App\Jobs;

use App\Libraries\HTTPUtil;
use Illuminate\Support\Facades\DB;
use Consatan;

class uploadImageJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $post_id;
    private $cdn_id;
    private $original_url;
    public function __construct($post_id,$cdn_id,$original_url)
    {
        //
        $this->post_id = $post_id;
        $this->cdn_id = $cdn_id;
        $this->original_url = $original_url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $post = DB::table('posts')->where('id',$this->post_id)->first();
        if(empty($post)) return;

        $img_url = "http://dcimg.awalker.jp/img1.php?id=".$this->cdn_id;
        $url_hash = md5($img_url);
        HTTPUtil::get($img_url, $url_hash);
        $img_url = "http://dcimg.awalker.jp/img2.php?sec_key=".$this->cdn_id;
        $img_file = HTTPUtil::get($img_url, $url_hash);
        if($img_file!=false) {
            file_put_contents("/tmp/".$url_hash.".jpg", $img_file);
            $file_size = filesize("/tmp/".$url_hash.".jpg");
            $size = getimagesize("/tmp/".$url_hash.".jpg");
            $weibo = new Consatan\Weibo\ImageUploader\Client();

            // 默认返回的是 https 协议的图床 URL，调用该方法返回的是 http 协议的图床 URL
            // $weibo->useHttps(false);

            // 上传示例图片
            $url = $weibo->upload("/tmp/".$url_hash.".jpg", 'prctrash@126.com', '0okmnji9');

            // 输出新浪图床 URL
            if(!empty($url)) {
                DB::table('post_images')->insert([
                    'post_id'=>$post->id,
                    'original_url'=>$this->original_url,
                    'original_url_hash'=>md5($this->original_url),
                    'url'=>$url,
                    'file_size'=>intval($file_size),
                    'width'=>intval($size[0]),
                    'height'=>intval($size[1]),
                    'created_at'=>date('Y-m-d H:i:s'),
                    'updated_at'=>date('Y-m-d H:i:s')
                ]);
            }
        }
        if(file_exists("/tmp/".$url_hash)) unlink("/tmp/".$url_hash);
        if(file_exists("/tmp/".$url_hash.".jpg")) unlink("/tmp/".$url_hash.".jpg");
    }

    public function failed(\Exception $exception)
    {
        // Send user notification of failure, etc...
        $msg = $exception->getMessage();
        dispatch( new sendUpdateMessageJob("309781356", "307558399", $msg, false) );
    }
}