<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2018/1/14
 * Time: 16:38
 */

namespace App\Jobs;

use App\Libraries\HTTPUtil;

class uploadOtherImages extends Job
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
        HTTPUtil::uploadNGZKImage($this->post_id, $this->cdn_id, $this->original_url);
    }

    public function failed(\Exception $exception)
    {
        // Send user notification of failure, etc...
        $msg = $exception->getMessage();
        dispatch( new sendUpdateMessageJob("309781356", "307558399", $msg, false) );
    }
}