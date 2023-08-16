<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\User;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth', ['except' => ['login']]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'message' => $validator->errors(),
                    'status' => 422,
                    'type' => 'HttpResponseException',
                ],
            ], 422);
        }


        $credentials = request(['email', 'password']);
  

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'error' => [
                    'message' =>
                    [
                        "error" => ['Your credentials do not match our records']
                    ],
                    'status' => 422,
                    'type' => 'HttpResponseException',
                ],
            ], 422);
        }
        // if (!$token = JWTAuth::attempt($credentials)) {
        //     return response()->json(['error' => 'Unauthorized'], 401);
        // }
        $user = User::where('email', $request->email)->first();
       
        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        $ttl = JWTAuth::factory()->getTTL() * 60;
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $ttl
        ]);
    }
}
