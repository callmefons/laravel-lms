<?php

namespace App\Http\Middleware;

use Closure;
use App\User;

class AuthenticationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try{
            $user = User::where('email', $request->email)->get();
            // Account activation status validation
            if(!$user[0]['activated']){
                return response()->json([
                    'status' => "failed",
                    'errormessage' => "please activated your account first!"
                ], 401);
            }elseif($user[0]['archived']){
                return response()->json([
                    'status' => "failed",
                    'errormessage' => "your account has been archived!"
                ], 401);
            }
            return $next($request);
        }
        catch(\Exception $e){
            return response()->json([   'status'=>'failed',
                'errorcode' => '12',
                'errormessage' => 'Your email or password does not match!'
            ]);
        }

    }
}
