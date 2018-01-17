<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2018/1/14
 * Time: 16:38
 */

namespace App\Jobs;

use App\Libraries\HTTPUtil;
use Consatan;
use Illuminate\Support\Facades\DB;

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
        $this->uploadNGZKImage($this->post_id, $this->cdn_id, $this->original_url);
    }

    private function uploadNGZKImage($post_id,$cdn_id,$original_url) {
        if(!empty($cdn_id)) {
            $img_url = "http://dcimg.awalker.jp/img1.php?id=".$cdn_id;
            $url_hash = md5($img_url);
            HTTPUtil::get($img_url, $url_hash);
            $img_url = "http://dcimg.awalker.jp/img2.php?sec_key=".$cdn_id;
            $img_file = HTTPUtil::get($img_url, $url_hash);
        } else {
            $url_hash = md5($original_url);
            $img_file = HTTPUtil::get($original_url, $url_hash);
        }

        if(empty($img_file)) return null;

        file_put_contents("/tmp/".$url_hash.".jpg", $img_file);
        $file_size = filesize("/tmp/".$url_hash.".jpg");
        $size = getimagesize("/tmp/".$url_hash.".jpg");


        // 默认返回的是 https 协议的图床 URL，调用该方法返回的是 http 协议的图床 URL
        // $weibo->useHttps(false);

        // 上传示例图片
        $weibo_client = new Consatan\Weibo\ImageUploader\Client();
        $url = $weibo_client->upload("/tmp/".$url_hash.".jpg", 'prctrash@126.com', '0okmnji9');

        if(file_exists("/tmp/".$url_hash)) unlink("/tmp/".$url_hash);
        if(file_exists("/tmp/".$url_hash.".jpg")) unlink("/tmp/".$url_hash.".jpg");

        // 输出新浪图床 URL
        if(empty($url)) return null;

        $img_data = [
            'post_id'=>$post_id,
            'original_url'=>$original_url,
            'original_url_hash'=>md5($original_url),
            'url'=>$url,
            'file_size'=>intval($file_size),
            'width'=>intval($size[0]),
            'height'=>intval($size[1]),
            'created_at'=>date('Y-m-d H:i:s'),
            'updated_at'=>date('Y-m-d H:i:s')
        ];
        if(empty($cdn_id)) {
            DB::table('ngzk_post_images')->insert($img_data);
        } else {
            DB::table('post_images')->insert($img_data);
        }

        return ['url'=>$url,'file_size'=>$file_size,'size'=>$size,'original_url'=>$original_url];
    }

    public function failed(\Exception $exception)
    {
        // Send user notification of failure, etc...
        $msg = $exception->getMessage();
        dispatch( new sendUpdateMessageJob("309781356", "307558399", $msg, false) );
    }
}