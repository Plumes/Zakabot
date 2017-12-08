<?php

use Illuminate\Database\Seeder;

class Hiragana2MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $now = date("Y-m-d H:i:s");
        DB::table("idol_members")->insert([
            ['group_id'=>1,"official_id"=>34,"name"=>"金村 美玖","last_post_at"=>$now,"created_at"=>$now,"updated_at"=>$now],
            ['group_id'=>1,"official_id"=>35,"name"=>"河田 陽菜","last_post_at"=>$now,"created_at"=>$now,"updated_at"=>$now],
            ['group_id'=>1,"official_id"=>36,"name"=>"小坂 菜緒","last_post_at"=>$now,"created_at"=>$now,"updated_at"=>$now],
            ['group_id'=>1,"official_id"=>37,"name"=>"富田 鈴花","last_post_at"=>$now,"created_at"=>$now,"updated_at"=>$now],
            ['group_id'=>1,"official_id"=>38,"name"=>"丹生 明里","last_post_at"=>$now,"created_at"=>$now,"updated_at"=>$now],
            ['group_id'=>1,"official_id"=>39,"name"=>"濱岸 ひより","last_post_at"=>$now,"created_at"=>$now,"updated_at"=>$now],
            ['group_id'=>1,"official_id"=>40,"name"=>"松田 好花","last_post_at"=>$now,"created_at"=>$now,"updated_at"=>$now],
            ['group_id'=>1,"official_id"=>41,"name"=>"宮田 愛萌","last_post_at"=>$now,"created_at"=>$now,"updated_at"=>$now],
            ['group_id'=>1,"official_id"=>42,"name"=>"渡邉 美穂","last_post_at"=>$now,"created_at"=>$now,"updated_at"=>$now],
        ]);
    }
}
