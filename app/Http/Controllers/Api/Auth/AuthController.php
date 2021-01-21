<?php

namespace App\Http\Controllers\Api\Auth;

use App\Api\ApiMessages;

use App\Dictionaries\UserDictionary;
use App\Dictionaries\LeadDictionary;
use App\Dictionaries\FollowUpDictionary;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\SignupRequest;
use App\Models\Enterprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct()
    {
    }

    public function login(AuthRequest $request)
    {
        try {
            $request['email'] = Str::lower($request['email']);
            
            if (!$token = Auth::guard('api')->attempt($request->only('email', 'password'))) {
                $message = new ApiMessages('Unauthorized');

                return response()->json($message->getMessage(), 401);
            } else {

                return $this->responseToken($token);
            }
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function logout()
    {
        try {
            Auth::guard('api')->logout();

            return response()->json(['message' => 'Logout successfully'], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function refresh()
    {
        try {
            $token = Auth::guard('api')->refresh();

            return $this->responseToken($token);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function signup(SignupRequest $request)
    {
        try {
            $request['name'] = Str::lower($request['name']);

            Enterprise::create($request->all())
                ->user()
                ->create([
                    'name' => Str::lower($request['name_user']),
                    'email' => Str::lower($request['email']),
                    'password' => bcrypt($request['password']),
                    'type' => UserDictionary::ADMINISTRATOR
                ]);

            return response()->json([
                'data' => [
                    'message' => 'Signup with success'
                ]
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function responseToken($token)
    {
        try {
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
                'user' => Auth::guard('api')->user(),
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }
}
