<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('teacher_id')->unsigned();
            $table->string('name');
            $table->string('description');
            $table->integer('start_xp')->default(1);
            $table->integer('leader_board')->default(1);
            $table->integer('status')->default(1);
            $table->timestamp('published_at');

            $table->timestamps();



        });

        Schema::table('courses',function (Blueprint $table)
        {
            $table->foreign('teacher_id')
                ->references('id')->on('teachers')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('courses');
    }
}
