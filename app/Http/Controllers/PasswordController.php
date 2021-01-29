<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use App\Http\Requests;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;
use Password;
use Hash;
use Auth;
use Mail;
use App\User;

class PasswordController extends Controller
{
    use ResetsPasswords;
    protected  $redirectTo = 'http://54.169.87.71/auth/signin';
    protected  $email_token;

    public function __construct()
    {
        $this->middleware('jwt.auth', [
            'only' => ''
        ]);
    }

    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $messages = $validator->errors();
        if ($validator->fails()) {
            if ($messages->has('token')) {
                return response()->json(['status'=>'failed',
                    'errorcode' => '32',
                    'errormessage' => $messages->first('token')]);
            }

            if ($messages->has('email')) {
                return response()->json(['status'=>'failed',
                    'errorcode' => '32',
                    'errormessage' => $messages->first('email')]);
            }

            if ($messages->has('password')) {
                return response()->json(['status'=>'failed',
                    'errorcode' => '32',
                    'errormessage' => $messages->first('password')]);
            }

            return response()->json(['status'=>'failed',
                'errorcode'=>'32',
                'errorname'=> $messages->all()]);
        }

        $credentials = $this->getResetCredentials($request);
        $broker = $this->getBroker();
        $response = Password::broker($broker)->reset($credentials, function ($user, $password) {
            $this->resetPassword($user, $password);
        });
        switch ($response) {
            case Password::PASSWORD_RESET:
                return $this->getResetSuccessResponse($response);
            default:
                return $this->getResetFailureResponse($request, $response);
        }
    }


    public function sendResetLinkEmail(Request $request)
    {
        $this->validateSendResetLinkEmail($request);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        $messages = $validator->errors();
        if ($validator->fails()) {
            if ($messages->has('email')) {
                return response()->json(['status'=>'failed',
                    'errorcode' => '32',
                    'errormessage' => $messages->first('email')]);
            }
        }

        $broker = $this->getBroker();
        $response = Password::broker($broker)->sendResetLink(
            $this->getSendResetLinkEmailCredentials($request),
            $this->resetEmailBuilder()
        );

        switch ($response) {
            case Password::RESET_LINK_SENT:
                return $this->getSendResetLinkEmailSuccessResponse($response);
            case Password::INVALID_USER:
            default:
                return $this->getSendResetLinkEmailFailureResponse($response);
        }
    }

    /**
     * Get the response for after the reset link has been successfully sent.
     *
     * @param  string  $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getSendResetLinkEmailSuccessResponse($response)
    {
        // return redirect()->back()->with('status', trans($response));
        return response()->json(['status'=>'success',
            'message'=>trans($response)], 200);
    }

    /**
     * Get the response for after the reset link could not be sent.
     *
     * @param  string  $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getSendResetLinkEmailFailureResponse($response)
    {
        // return redirect()->back()->withErrors(['email' => trans($response)]);
        return response()->json(['status'=>'failed',
            'errorcode'=>'62',
            'errormessage'=>trans($response)]);
    }

    public function showResetForm(Request $request, $token = null)
    {
        if(is_null($token))
        {
            return response()->json(['status'=>'unauthorized',
                'errormessage'=>'Token for reset password is required!'], 401);
        }
        $email = $request->input('email');
//        $user=User::where('email','=',$email)->first();
//        $userToken=JWTAuth::fromUser($user);


        if(property_exists($this, 'resetView')){
            return view($this->resetView)->with(compact('token','email'));
        }
//        // Call Front-End page
//        if (is_null($token)) {
//            return response()->json(['status'=>'unauthorized',
//                'errormessage'=>'Token for reset password is required!'], 401);
//        }
        // return response()->json(['status'=>'success',
        //                           'token'=> $token,
        //                            'email'=> $request->email ], 200);
        if(view()->exists('auth.passwords.reset')){
            return view('auth.passwords.reset', compact('token', 'email'));
        }
    }



}
