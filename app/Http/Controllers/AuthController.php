<?php

namespace App\Http\Controllers;
use App\Modules\ActivationService;
use Auth;

use App\User;

use Illuminate\Http\Request;


use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{

    public function __construct(ActivationService $activationService)
    {
        $this->middleware('inactive_user_auth', ['only' => 'signin']);
        $this->middleware('jwt.auth', [
            'only' => [
            ]
        ]);

        $this->activationService = $activationService;
    }

    public function store(Request $request)
    {

    }

    public function registration(Request $request)
    {

        $password = $request->input('password');
        $email = $request->input('email');
        $image = $request->input('image');
        $title = $request->input('title');
        $name = $request->input('name');
        $position = $request->input('position');
        $id_card = $request->input('id_card');
        $phone = $request->input('phone');
        $address = $request->input('address');
        $teaching_level = $request->input('teaching_level');
        $institution = $request->input('institution');
        $province = $request->input('province');

        $fileName = $name . date('-Ymd-His-') . ".png";
        $role = "teacher";
        $decodedImg = $this->base64_to_jpeg($image, $fileName, $role);

            $user = new User([
                'password' => bcrypt($password),
                'email' => $email,
                'role' => 'teacher'
            ]);
            $user->save();

            $user = User::where('email', $email)->first();

            $user->teacher()->create([
                'image' => $decodedImg,
                'title' => $title,
                'name' => $name,
                'position' => $position,
                'id_card' => $id_card,
                'phone' => $phone,
                'address' => $address,
                'teaching_level' => $teaching_level,
                'institution' => $institution,
                'province' => $province
            ]);

        $email = User::where('email', $email)->get()->toArray();
        $this->activationService->sendActivationMail($email);


        return response()->json(['status'=>'success'], 200);

    }

    protected $redirectURL = "http://localhost:3000/#/login";
    public function activateUser($token)
    {
        if ($user = $this->activationService->activateUser($token)) {
            return redirect($this->redirectURL);
        }
        return response()->json(['status'=>'inactive',
            'errorcode'=>'62',
            'errormessage'=>'Invalid confirmation link']);
    }


    public function signin(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            // verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                // return response()->json(['error' => 'invalid_credentials'], 401);
                return response()->json([   'status'=>'failed',
                    'errorcode' => '12',
                    'errormessage' => 'Your email or password does not match!']);
            }
        } catch (\Exception $e) {
            // something went wrong
            return response()->json([   'status' => 'failed',
                'errorcode' => '12',
                'errormessage' => 'Could not create token!'

            ]);
        }

        // if no errors are encountered we can return a JWT
        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'token' => $token,
                    'id' => Auth::user()->id,
                    'name' => Auth::user()->name,
                    'email'=> Auth::user()->email,
                    'role' => Auth::user()->role
                ]

            ], 200);
    }



    public function base64_to_jpeg($base64_string, $output_file, $role) {

        switch ($role){
            case "teacher" :
                $destination = public_path() . '/teachers/logo/' . $output_file;
                break;
        }

        $ifp = fopen($destination, "wb");

        $data = explode(',', $base64_string);

        fwrite($ifp, base64_decode($data[1]));

        fclose($ifp);

        return $output_file;
    }
}
