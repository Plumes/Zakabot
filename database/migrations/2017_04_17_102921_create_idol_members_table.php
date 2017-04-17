<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIdolMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('idol_members', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id')->default(0);
            $table->string('official_id');
            $table->string('name');
            $table->dateTimeTz("last_post_at");
            $table->timestamps();

            $table->index('official_id');
            $table->index('group_id');
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
        Schema::drop('idol_members');
    }
}
