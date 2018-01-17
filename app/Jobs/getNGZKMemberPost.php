<?php
/**
 * Created by PhpStorm.
 * User: plume
 * Date: 2018/1/17
 * Time: 14:29
 */


namespace App\Jobs;

class getNGZKMemberPost extends Job
{
    private $url;
    private $page;
    private $month;
    private $member_id;
    /**
     * Create a new job instance.
     *
     * @return void
     * @param int $member_id
     * @param string $url blog link
     * @param int page 1
     * @param string $month "201701"
     */
    public function __construct(int $member_id, string $url,int $page,string $month)
    {
        //
        $this->member_id = $member_id;
        $this->page = $page;
        $this->month = $month;
        $this->url = $this->url."?d=".$month."&p=".$page;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $page_html = file_get_contents($this->url);
        $page = new \DOMDocument();
        $res = $page->loadHTML($page_html, LIBXML_PARSEHUGE);
        if(!$res) return;
        $main_html = $page->getElementById("sheet")

    }
}
