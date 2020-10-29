<?php

namespace App\Http\Controllers\Api\Auth;

use App\Api\ApiMessages;
use App\Http\Controllers\Controller;
use App\Models\Enterprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->all(['email','password']);

        Validator::make($credentials, [
            'email' => 'required|string',
            'password' => 'required|string|min:8'
        ])->validate();

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            $message = new ApiMessages('Unauthorized');

            return response()->json($message->getMessage(), 401);
        } else {
            
            return $this->responseToken($token);
        }
    }

    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Logout successfully'], 200);
    }

    public function refresh()
    {
        $token = Auth::guard('api')->refresh();

        return $this->responseToken($token);
    }

    public function signup(Request $request)
    {
        $data = $request->all();

        Validator::make($data,[
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8'
        ])->validate();

        if (!$request->has('password') || !$request->get('password')) {
            $message = new ApiMessages('You need to have a password');
            
            return response()->json($message->getMessage(), 401);
        }

        try {
            Enterprise::create($data)
                ->user()
                ->create(
                    [
                        'name' => $data['name_user'],
                        'email' => $data['email'],
                        'password' => bcrypt($data['password']),
                        'type' => "administrador"
                    ]
                );

            return $this->login($request);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    //returno do token
    public function responseToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => Auth::guard('api')->user(),
        ], 200);
    }
}
