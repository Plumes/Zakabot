<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNgzkPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ngzk_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('member_id');
            $table->string('title');
            $table->string('url');
            $table->string('cover_image')->nullable();
            $table->string('preview')->nullable();
            $table->text('content');
            $table->dateTime("posted_at");
            $table->string('url_hash' , 64);
            $table->string('cover_image_hash' , 64)->nullable("用于删除上传至sm.ms的图片");

            $table->timestamps();
            $table->unique('url_hash');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ngzk_posts');
    }
}
