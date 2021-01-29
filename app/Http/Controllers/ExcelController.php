<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Course;
use App\Level;
use App\Student;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_Style_Border;
use PHPExcel_Style_Protection;

class ExcelController extends Controller
{

    public function __construct()
    {

    }

    public function importExport(){
        return view('excel.import');
    }

    public function downloadExcel($id)
    {
        $course = Course::find($id);
        $students = $course->students()->select('course_id','id','student_id','name','overall_xp')->get();

        $export = User::select('id','email')->get();
        //$data = count($students);

        Excel::create('export_data_lms', function ($excel) use ($students){
            $excel->sheet('Sheet 1' , function ($sheet) use($students) {
                $data = count($students);
                //$sheet->freezeFirstRow();
                $sheet->fromArray($students);
            });
        })->export('xlsx');

    }

    public function importExcel(Request $request){


        Excel::load(Input::file('file'), function ($reader){
            $reader->each(function ($sheet){
               foreach ($sheet->toArray() as $row){

                    $levels = Level::where('course_id', '=', $sheet->course_id)->get();
                       $ceiling_xp = $levels->max('ceiling_xp');

                   $student = Student::find($sheet->id);

                   if($sheet->overall_xp < $ceiling_xp){
                       $student->overall_xp = $sheet->overall_xp;
                   }else{
                       $student->overall_xp = $ceiling_xp;
                   }

                   $student->save();
               }
            });
        });

//        return response()->json(
//            [
//                'status' => 'success'
//
//            ], 200);
        return redirect('http://54.169.87.71/course/edit-student-score');

    }




    public function export()
    {

    }
}
