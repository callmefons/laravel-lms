<?php

namespace App\Http\Controllers;

use App\Badge;
use App\Course;
use App\Level;
use App\Student;
use App\Teacher;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class StudentController extends Controller
{

    public function __construct()
    {

        $this->middleware('jwt.auth', [
            'only' => [
                'getBadgeOfStudent',
                'deleteBadgeOfStudent',
                'editProfilebyTeacher',
                'updateScoreStudent',
                'updateScoreAndBadgeStudent',
                'deleteStudentOfCourse',

                'getStudentProfile',
                'editProfile'
            ]
        ]);

        $this->middleware('teacher', [
            'except' => [
                'getStudentProfile',
                'editProfile'
            ]
        ]);

        $this->middleware('student', [
            'only' => [
                'getStudentProfile',
                'editProfile'
            ]
        ]);

    }

    public function getStudentProfile($id)
    {
        if(Auth::user()->id == $id){
            $student = Student::where('user_id', '=', Auth::user()->id)->first();
            $course = Course::find($student->course_id);
            $teacher = Teacher::find($course->teacher_id)
                ->get(['title', 'name', 'position', 'image']);

            $tempStudent = array();
            array_push($tempStudent,array('student' => $student, 'badge' => $student->badges));

            $leader_board = $course->leader_board;

            $student_leaderboard = $course->students()
                ->orderBy('overall_xp', 'desc')
                ->limit($leader_board)->get();

            $tempLeaderboard = array();

            foreach ($student_leaderboard as $student)
            {
                $student = Student::where('id', '=', $student['id'])->first();
                $badge = $student->badges;

                array_push($tempLeaderboard, array('student' => $student, 'badge' => $badge));

            }


            return response()->json(
                [
                    'status' => 'success',
                    'data' => [
                        'student' => $tempStudent,
                        'course' => $course,
                        'teacher' => $teacher,
                        'leaderboard' => $tempLeaderboard
                    ]
                ], 200);

        }else{
            return response()->json(
                [
                    'status' => 'cant get student profile'
                ],200);
        }

    }

    public function editProfile(Request $request)
    {
        $id = $request->input('id');
        $student = Student::where('id', '=', $id)->first();
        $student->name = $request->input('name');
        $student->student_id = $request->input('student_id');

        $image = $request->input('image');
        if (preg_match("~data:image/[a-zA-Z]*;base64,~", $image)) {
            $destination = '/students/logo/';
            $old_image = $student->image;
            $file = public_path() . $destination . $old_image;
            File::Delete($file);
            $fileName = $request->input('name') . date('-Ymd-His-') . ".png";
            $decodedImg = $this->base64_to_jpeg($image, $fileName, $destination);
            $student->image = $decodedImg;
        } else {
            $student->image = $image;
        }

        $student->save();

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'student' => $student,
                ]
            ], 200);

    }

    public function getBadgeOfStudent($id)
    {

        $student = Student::where('id', '=', $id)->first();
        $badge = $student->badges;



        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'badge' => $badge
                ]
            ], 200);

    }

    public function deleteBadgeOfStudent(Request $request)
    {
        $student = Student::findOrFail($request->input('id'));

        if ($request->has('badges')) {
            foreach ($request->input('badges') as $badge) {
                $student->badges()->detach($badge['id']);
            }
        }

        return response()->json(
            [
                'status' => 'success'
            ], 200);


    }

    public function editProfilebyTeacher(Request $request)
    {
        $course_id = $request->input('course_id');
        $id = $request->input('id');
        $student = Student::where('id', '=', $id)->first();
        $levels = Level::where('course_id', '=', $course_id)->get();


        $image = $request->input('image');
        if (preg_match("~data:image/[a-zA-Z]*;base64,~", $image)) {
            $destination = '/students/logo/';
            $old_image = $student->image;
            $file = public_path() . $destination . $old_image;
            File::Delete($file);
            $fileName = $request->input('name') . date('-Ymd-His-') . ".png";
            $decodedImg = $this->base64_to_jpeg($image, $fileName, $destination);
            $student->image = $decodedImg;
        } else {
            $student->image = $image;
        }


        $overall_xp = $request->input('overall_xp');
        foreach ($levels as $level) {
            if (($overall_xp >= $level->floor_xp) && ($overall_xp <= $level->ceiling_xp)) {
                $student_level = $level->level_id;
            }else{
                $student_level = $level->level_id;
            }
        }

        $student->student_id = $request->input('student_id');
        $student->name = $request->input('name');

        $student->overall_xp = $overall_xp;
        $student->level = $student_level;

        $student->save();

        $badge = $student->badges;

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'student' => $student,
                    'badge' => $badge,
                    '$student->image' => $student->image
                ]
            ], 200);

    }

    public function updateScoreStudent(Request $request)
    {
        $course_id = $request->input('course_id');
        $score = $request->input('score');
        $max_score = $request->input('max_score');
        $students = $request->input('students');
        $levels = Level::where('course_id', '=', $course_id)->get();
        foreach ($students as $student) {
            $student_update = Student::where('id', '=', $student['id'])->first();

            $new_score = $student_update->overall_xp += $score;
            if($new_score < $max_score)
            {
                $temp = $new_score;
                $student_update->overall_xp = $new_score;
            }else{
                $temp = $max_score;
                $student_update->overall_xp = $max_score;
            }


            foreach ($levels as $level) {
                if (($temp >= $level->floor_xp) && ($temp <= $level->ceiling_xp)) {
                    $student_update->level = $level->level_id;
                } elseif ($temp >= $level->ceiling_xp) {
                    $student_update->level = $level->level_id;
                }
            }
            $student_update->save();

        }

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'students' => $student_update
                ]
            ], 200);


    }

    public function updateScoreAndBadgeStudent(Request $request)
    {

        $course_id = $request->input('course_id');
        $badge_id = $request->input('badge_id');
        $score = $request->input('score');

        $students = $request->input('students');
        $levels = Level::where('course_id', '=', $course_id)->get();
        foreach ($students as $student) {

            $student_update = Student::where('id', '=', $student['id'])->first();

            if (!$student_update->badges->contains($badge_id)) {
                $student_update->badges()->attach($badge_id);
            }

            $temp = $student_update->overall_xp += $score;
            foreach ($levels as $level) {
                if (($temp >= $level->floor_xp) && ($temp <= $level->ceiling_xp)) {
                    $student_update->level = $level->level_id;
                } elseif ($temp >= $level->ceiling_xp) {
                    $student_update->level = $level->level_id;
                }
            }
            $student_update->save();

        }


        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'students' => $student_update
                ]
            ], 200);


    }


    public function deleteStudentOfCourse(Request $request)
    {
        $course_id = $request->input('course_id');
        $students = $request->input('students');
        $course = Course::find($course_id);

        $destination = '/students/logo/';

        foreach ($students as $student) {
            $student_update = $course->students()->where('user_id', '=', $student['user_id'])->first();

            $old_image = $student_update->image;
            $file = public_path() . $destination . $old_image;
            File::Delete($file);

            $student_update->delete();


        }

        return response()->json(
            [
                'status' => 'success'
            ], 200);


    }

    public function base64_to_jpeg($base64_string, $output_file, $destination)
    {

        $destination = public_path() . $destination . $output_file;

        $ifp = fopen($destination, "wb");

        $data = explode(',', $base64_string);

        fwrite($ifp, base64_decode($data[1]));

        fclose($ifp);

        return $output_file;
    }

}
