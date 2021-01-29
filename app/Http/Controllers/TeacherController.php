<?php

namespace App\Http\Controllers;

use App\Teacher;
use App\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\File;
use JWTAuth;
use Auth;
use DB;

class TeacherController extends Controller
{
    public function __construct()
    {

        $this->middleware('jwt.auth', [
            'only' => [
                'index',
                'editProfileOfUser',
                'deleteUser'
            ]
        ]);

        $this->middleware('teacher');

    }


    public function index()
    {
        $teacher = Teacher::where('user_id', '=', Auth::user()->id)->first();
        return response()->json([
            'status' => 'success',
            'data' => [
                'teacher_profile' => [
                    [
                        'id' => $teacher->id,
                        'email' => Auth::user()->email,
                        'image' => $teacher->image,
                        'title' => $teacher->title,
                        'address' => $teacher->address,
                        'name' => $teacher->name,
                        'position' => $teacher->position,
                        'id_card' => $teacher->id_card,
                        'phone' => $teacher->phone,
                        'institution' => $teacher->institution,
                        'province' => $teacher->province,
                        'teaching_level' => $teacher->teaching_level
                    ],

                ],
            ]
        ], 200);

    }

    public function editProfileOfUser(Request $request)
    {
        $id = $request->input('id');
        $user = Teacher::where('id', '=', $id)->first();

        $image = $request->input('image');
        $user->title = $request->input('title');
        $user->name = $request->input('name');
        $user->position = $request->input('position');
        $user->id_card = $request->input('id_card');
        $user->phone = $request->input('phone');
        $user->address = $request->input('address');
        $user->teaching_level = $request->input('teaching_level');
        $user->institution = $request->input('institution');
        $user->province = $request->input('province');

        if(preg_match("~data:image/[a-zA-Z]*;base64,~", $image)){
            $destination = '/teachers/logo/';
            $old_image = $user->image;
            $file = public_path() . $destination . $old_image;
            $old_image_deleted = File::Delete($file);

            $fileName = $user->name . date('-Ymd-His-') . ".png";
            $decodedImg = $this->base64_to_jpeg($image, $fileName, $destination);

            $user->image = $decodedImg;
        }else{
            $user->image = $image;
        }

        $user->save();

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'user' => $user
                ]
            ], 200);

    }

    public function deleteUser($id)
    {
        $user = User::find($id)->first();

        $destination = '/teachers/logo/';
        $old_image = $user->image;
        $file = public_path() . $destination . $old_image;
        File::Delete($file);

        $user->delete();


        return response()->json(
            [
                'status' => 'success',
                'user deleted' => $user
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
