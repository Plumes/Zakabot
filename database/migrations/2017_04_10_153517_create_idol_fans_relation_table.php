<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIdolFansRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('idol_fans_relation', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fan_id');
            $table->string('chat_id');
            $table->integer('member_id');
            $table->timestamps();

            $table->index('member_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop('idol_fans_relation');
    }
}
