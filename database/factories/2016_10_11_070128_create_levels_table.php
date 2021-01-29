<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->integer('level_id');
            $table->integer('course_id')->unsigned();
            $table->string('floor_xp');
            $table->string('ceiling_xp');

            $table->timestamps();

            $table->foreign('course_id')
                ->references('id')
                ->on('courses');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('levels');
    }
}
