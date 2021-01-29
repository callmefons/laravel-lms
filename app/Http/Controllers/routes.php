<?php
header('Access-Control-Allow-Origin:  *');
header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers:  Origin, X-Requested-With, Content-Type, Accept, Authorization, X-CSRF-Token, X-XSRF-Token');


Route::group(['prefix' => 'api/v1'], function(){


    //Permission Required
    Route::get('course', 'CourseController@getAllCourse');
    Route::get('courses/status/{id}','CourseController@getCourseByStatus');
    Route::get('course/{id}', 'CourseController@getCourseById');
    Route::post('course/create', 'CourseController@createCourse');
    Route::post('course/add/students','CourseController@updateStudentsOfCourse');
    Route::put('course/status','CourseController@updateStatusCourse');
    Route::put('course/edit', 'CourseController@editCourse');
    Route::post('course/setting', 'CourseController@settingCourse');
    Route::post('course/create/badge', 'CourseController@createBadge');
    Route::put('course/edit/badge', 'CourseController@editBadge');
    Route::get('course/{id}/badge', 'CourseController@getBadgeOfCourse');
    Route::delete('course/delete/badge/{id}', 'CourseController@deleteBadge');
    Route::get('course/highscore/{id}','CourseController@getHighScoreOfCourse');

    //Student Api

    //Student
    Route::get('student/{id}','StudentController@getStudentProfile');
    Route::put('student/edit', 'StudentController@editProfile');

    //Teacher
    Route::get('student/{id}/badge','StudentController@getBadgeOfStudent');
    Route::post('student/delete/badge','StudentController@deleteBadgeOfStudent');
    Route::put('student/edit/profile', 'StudentController@editProfilebyTeacher');
    Route::post('students/update/score', 'StudentController@updateScoreStudent');
    Route::post('students/update/scoreandbadge', 'StudentController@updateScoreAndBadgeStudent');
    Route::post('students/delete', 'StudentController@deleteStudentOfCourse');
    Route::get('teacher',['uses' => 'TeacherController@index']);
    Route::put('teacher/edit/profile', 'TeacherController@editProfileOfUser');
    Route::delete('teacher/delete/{id}', 'TeacherController@deleteUser');

    //Post Api
    Route::post('post/create', 'PostController@createPost');
    Route::post('post/comment', 'PostController@commentPost');
    Route::get('post/course/{id}', 'PostController@getPost');
    Route::post('post/comment/edit','PostController@editComment');
    Route::post('post/comment/delete','PostController@deleteComment');

    //Authen Api
    Route::post('user/registration',[
        'uses' => 'AuthController@registration'
    ]);

	Route::post('user/signin',[
		'uses' => 'AuthController@signin'
		]);

    Route::post('student/signin','AuthController@studentsignin');
});