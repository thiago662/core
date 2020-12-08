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
        try {
            $request['email'] = mb_strtolower($request['email'], 'UTF-8');
            $credentials = $request->all(['email', 'password']);

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

    public function signup(Request $request)
    {
        try {
            $request['name'] = mb_strtolower($request['name'], 'UTF-8');
            $data = $request->all();

            Validator::make($data, [
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8',
                'password_confirmation' => 'required|string|min:8'
            ])->validate();

            if (
                $request->has('password') && $request->has('password_confirmation') &&
                $request->get('password') == $request->get('password_confirmation')
            ) {
                Enterprise::create($data)
                    ->user()
                    ->create([
                        'name' => mb_strtolower($data['name_user'], 'UTF-8'),
                        'email' => mb_strtolower($data['email'], 'UTF-8'),
                        'password' => bcrypt($data['password']),
                        'type' => "administrador"
                    ]);

                return response()->json([
                    'data' => [
                        'msg' => 'Signup with success'
                    ]
                ], 200);
            }
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    //returno do token
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
