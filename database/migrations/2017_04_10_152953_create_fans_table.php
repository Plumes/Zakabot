<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('fans', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->nullable();
            $table->string('chat_id');
            $table->string('telegram_user_id');
            $table->timestamps();

            $table->index('chat_id');
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
        Schema::drop('fans');
    }
}
