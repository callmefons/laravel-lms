<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('course_id')->unsigned();
            $table->integer('student_id');
            $table->string('name');
            $table->string('image');
            $table->string('username');
            $table->string('password');
            $table->integer('overall_xp');
            $table->integer('level');

            $table->timestamps();

            $table->foreign('course_id')
                ->references('id')
                ->on('courses');

        });


//        Schema::create('course_student', function (Blueprint $table) {
//
//            $table->integer('course_id')->unsigned()->index();
//            $table->foreign('course_id')
//                ->references('id')
//                ->on('courses')
//                ->onDelete('cascade');
//
//            $table->integer('student_id')->unsigned()->index();
//            $table->foreign('student_id')
//                ->references('id')
//                ->on('students')
//                ->onDelete('cascade');
//
//            $table->timestamps();
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('students');
    }
}
