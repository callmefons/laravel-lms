<?php

namespace App\Http\Controllers;
use App\Course;
use App\Modules\ActivationService;
use App\User;
use App\Badge;
use App\Level;
use App\Teacher;
use App\Student;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\File;
use JWTAuth;

use Auth;
use DB;

class CourseController extends Controller
{
    public function __construct(ActivationService $activationService)
    {
        $this->activationService = $activationService;
        $this->middleware('jwt.auth', [
            'only' => [
                'createCourse',
                'getAllCourse',
                'getCourseById',
                'getCourseByStatus',
                'editCourse',
                'settingCourse',
                'updateStudentsOfCourse',
                'updateStatusCourse',
                'getBadgeOfCourse',
                'createBadge',
                'editBadge',
                'deleteBadge',
                'getHighScoreOfCourse'
            ]
        ]);

    }

    public function createCourse(Request $request)
    {
        $course = new Course([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'start_xp' => $request->input('start_xp'),
            'leader_board' => $request->input('leader_board')
        ]);

        $teacher = Auth::user()->teacher()->get()->toArray();
        $teacher_course = Teacher::find($teacher[0]['id'])->first();
        $teacher_course->courses()->save($course);
//
        $courseLevels = array();

        if ($request->has('levels')) {
            foreach ($request->input('levels') as $level) {
                $levelObject = new Level([
                    'level_id' => $level['level_id'],
                    'floor_xp' => $level['floor_xp'],
                    'ceiling_xp' => $level['ceiling_xp'],
                ]);
                array_push($courseLevels, $levelObject);
            }
            $course->levels()->saveMany($courseLevels);
        }

        $courseStudents = array();

        if ($request->has('students')) {
            foreach ($request->input('students') as $student) {
                $image = $student['image'];
                $fileName = $student['name'] . date('-Ymd-His-') . ".png";
                $destination = '/students/logo/';
                $decodedImg = $this->base64_to_jpeg($image, $fileName, $destination);

                $password_gen = $this->generateStringRandom(10);
                $user = new User([
                    'password' => bcrypt($password_gen),
                    'email' => $this->generateStringRandom(10),
                    'role' => 'student',
                    'activated' => 1
                ]);
                $user->save();

                $studentObject = new Student([
                    'student_id' => $student['student_id'],
                    'name' => $student['name'],
                    'image' => $decodedImg,
                    'username' => $user->email,
                    'password' => $password_gen,
                    'overall_xp' => $student['overall_xp'],
                    'level' => $student['level']
                ]);
                $studentObject->user()->associate($user);
                $studentObject->course()->associate($course);
                $studentObject->save();

            }

//            $course->students()->saveMany($courseStudents);
        }

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'course' => $course
                ]
            ], 200);
    }


    public function getAllCourse(Request $request)
    {

        $teacher = Auth::user()->teacher()->first();
        $courses = Course::where('teacher_id','=',$teacher->id)->get();

        return response()->json(['data' => $courses], 200);
    }

    public function getCourseByStatus($id)
    {
        $courses = Auth::user()->courses()
            ->where('status', $id)
            ->get();
        return response()->json([
            'status' => 'success',
            'data' => $courses
        ], 200);
    }

    public function getCourseById($id)
    {
        $course = Course::find($id);
        $students = Student::where('course_id', '=', $id)->get();
        $levels = Level::where('course_id', '=', $id)->get();
        $badges = Badge::where('course_id', '=', $id)->get();


        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'course' => $course,
                    'students' => $students,
                    'levels' => $levels,
                    'badges' => $badges
                ]
            ], 200);
    }

    public function editCourse(Request $request)
    {
        $course_id = $request->input('course_id');
        $name = $request->input('name');
        $description = $request->input('description');
        $course = Course::find($course_id);
        $course->name = $name;
        $course->description = $description;
        $course->save();

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'course' => $course
                ]
            ], 200);
    }

    public function settingCourse(Request $request)
    {
        $course_id = $request->input('course_id');
        $start_xp = $request->input('start_xp');
        $leader_board = $request->input('leader_board');
        $students_level = $request->input('students_level');

        $course = Course::find($course_id);
        $course->start_xp = $start_xp;
        $course->leader_board = $leader_board;
        $course->save();

        Level::where('course_id', '=', $course_id)->delete();

        $courseLevels = array();

        if ($request->has('levels')) {
            foreach ($request->input('levels') as $level) {
                $levelObject = new Level([
                    'level_id' => $level['level_id'],
                    'floor_xp' => $level['floor_xp'],
                    'ceiling_xp' => $level['ceiling_xp'],
                ]);
                array_push($courseLevels, $levelObject);
            }
            $course->levels()->saveMany($courseLevels);
        }

        $levels = Level::where('course_id', '=', $course_id)->get();
        $students = Student::where('course_id', '=', $course_id)->get();
        //$overall_xp = $request->input('overall_xp');
        foreach ($students as $student) {
           foreach ($levels as $level)
           {
               if (($student['$overall_xp'] >= $level->floor_xp) && ($student['$overall_xp'] <= $level->ceiling_xp)) {
                   $student_level = $level->level_id;
                   Student::find($student['id'])->update(array('level' => $student_level));

               }else{
                   $student_level = $level->level_id;
                   Student::find($student['id'])->update(array('level' => $student_level));               }
           }
        }

//            ->update(array('level' => $students_level));



        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'course' => $course
                ]
            ], 200);
    }

    public function updateStudentsOfCourse(Request $request)
    {
        $course_id = $request->input('course_id');
        $course = Course::find($course_id);
        $start_xp = $course->start_xp;
        $levels = Level::where('course_id', '=', $course_id)->get();

        foreach ($levels as $level) {
            if (($start_xp >= $level->floor_xp) && ($start_xp <= $level->ceiling_xp)) {
                $student_level = $level->level_id;
            }
        }

        $courseStudents = array();

        if ($request->has('students')) {
            foreach ($request->input('students') as $student) {

                $user = new User([
                    'password' => $this->generateStringRandom(10),
                    'email' => $this->generateStringRandom(10),
                    'role' => 'student'
                ]);
                $user->save();

                $image = $student['image'];

                $fileName = $student['name'] . date('-Ymd-His-') . ".png";
                $destination = '/students/logo/';
                $decodedImg = $this->base64_to_jpeg($image, $fileName, $destination);


                $studentObject = new Student([
                    'student_id' => $student['student_id'],
                    'name' => $student['name'],
                    'image' => $decodedImg,
                    'username' => $user->email,
                    'password' => $user->password,
                    'overall_xp' => $start_xp,
                    'level' => $student_level
                ]);
                $studentObject->user()->associate($user);
                $studentObject->course()->associate($course);
                $studentObject->save();
//                array_push($courseStudents, $studentObject);
            }
//            $course->students()->saveMany($courseStudents);
        }


        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'course' => $course
                ]
            ], 200);
    }

    public function updateStatusCourse(Request $request)
    {
        $course_id = $request->input('course_id');
        $status_id = $request->input('status_id');

        $course = Course::find($course_id);
        $course->status = $status_id;
        $course->save();

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'course' => $course
                ]
            ], 200);
    }


    public function getBadgeOfCourse($id)
    {
        $course = Course::find($id);
        $badge = $course->badges()->get();

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'course' => $course,
                    'badge' => $badge
                ]
            ], 200);

    }

    public function createBadge(Request $request)
    {
        $course_id = $request->input('course_id');
        $course = Course::find($course_id);
        $image = $request->input('image');
        $fileName = $request->input('name') . date('-Ymd-His-') . ".png";
        $destination = '/students/badges/';
        $decodedImg = $this->base64_to_jpeg($image, $fileName, $destination);

        $badge = new Badge([
            'name' => $request->input('name'),
            'image' => $decodedImg,
            'xp' => $request->input('xp')
        ]);

        $course->badges()->save($badge);

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'badge' => $badge
                ]
            ], 200);

    }

    public function editBadge(Request $request)
    {
        $id = $request->input('id');

        $image = $request->input('image');

        if (preg_match("~data:image/[a-zA-Z]*;base64,~", $image)) {
            $fileName = $request->input('name') . date('-Ymd-His-') . ".png";
            $destination = '/students/badges/';
            $decodedImg = $this->base64_to_jpeg($image, $fileName, $destination);

            $old_badge = Badge::where('id', '=', $id)->first();
            $old_image = $old_badge->image;
            $file = public_path() . $destination . $old_image;
            File::Delete($file);

            $badge = Badge::where('id', '=', $id)->update(array(
                'name' => $request->input('name'),
                'image' => $decodedImg,
                'xp' => $request->input('xp')));

        } else {
            $badge = Badge::where('id', '=', $id)->update(array(
                'name' => $request->input('name'),
                'image' => $image,
                'xp' => $request->input('xp')));
        }


        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'badge' => $badge
                ]
            ], 200);

    }

    public function deleteBadge($id)
    {
        $destination = '/students/badges/';
        $old_badge = Badge::where('id', '=', $id)->first();
        $old_image = $old_badge->image;
        $file = public_path() . $destination . $old_image;
        File::Delete($file);

        $result = $old_badge->delete();


        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'result deleted' => $result
                ]
            ], 200);

    }

    public function getHighScoreOfCourse($id)
    {
        $course = Course::find($id);
        $leader_board = $course->leader_board;

        $students = $course->students()
            ->orderBy('overall_xp', 'desc')
            ->limit($leader_board)->get();

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'course' => $course->id,
                    'students' => $students
                ]
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

    public function generateStringRandom($number)
    {
        return str_random($number);
    }
}