<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Api\ApiMessages;
use App\Api\functions;
use App\Http\Requests\UserRequest;
use App\Models\Lead;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public $func;
    private $user;

    public function __construct(User $user)
    {
        $this->func = new functions();
        $this->user = $user;
        $this->middleware('administrator')->only([
            'index',
            'store',
            'destroy',
            'filter',
            'clerks'
        ]);
    }

    public function index()
    {
        try {
            $user = auth('api')->user();
            $users = $this->user
                ->where('enterprise_id', $user->enterprise_id)
                ->where('id', '!=', $user->id)
                ->orderBy('id');

            return response()->json($users->get(), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function store(UserRequest $request)
    {
        try {
            $user = auth('api')->user();
            $request['name'] = mb_strtolower($request['name'], 'UTF-8');
            $request['email'] = mb_strtolower($request['email'], 'UTF-8');
            $data = $request->all();

            Validator::make($data, [
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8'
            ])->validate();

            $data['password'] = bcrypt($data['password']);
            $data['enterprise_id'] = $user->enterprise_id;

            $this->user->create($data);

            return response()->json([
                'data' => [
                    'msg' => 'User created with success'
                ]
            ], 201);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function show($id)
    {
        try {
            $user = auth('api')->user();
            if ($this->func->admin($user)) {
                $users = $this->user
                    ->where('enterprise_id', $user->enterprise_id)
                    ->find($id);

                if ($users != []) {
                    return response()->json($users, 200);
                }
            }
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request['name'] = mb_strtolower($request['name'], 'UTF-8');
            $request['email'] = mb_strtolower($request['email'], 'UTF-8');
            $data = $request->all();

            Validator::make($data, [
                'email' => 'email|unique:users,email,' . $id . ',id',
            ])->validate();

            if (
                $request->has('password') && $request->has('password_confirmation') &&
                $request->get('password') == $request->get('password_confirmation')
            ) {
                $data['password'] = bcrypt($data['password']);
            } else {
                unset($data['password']);
            }

            $this->user
                ->findOrFail($id)
                ->update($data);

            return response()->json([
                'data' => [
                    'msg' => 'User updated with success'
                ]
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function destroy($id)
    {
        try {
            $this->user
                ->findOrFail($id)
                ->update(['email' => null])
                ->delete();

            return response()->json([
                'data' => [
                    'msg' => 'User deleted with success'
                ]
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function deleteMove(Request $request, $id)
    {
        try {
            Lead::where('user_id', $id)
                ->whereIn('status', [0, 1])
                ->update(['user_id' => $request['id']]);

            $this->user
                ->findOrFail($id)
                ->update(['email' => null]);
            $this->user
                ->findOrFail($id)
                ->delete();

            return response()->json([
                'data' => [
                    'msg' => 'User deleted with success'
                ]
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function filter(Request $request)
    {
        try {
            $request['name'] = mb_strtolower($request['name'], 'UTF-8');
            $request['email'] = mb_strtolower($request['email'], 'UTF-8');
            $request['type'] = mb_strtolower($request['type'], 'UTF-8');
            $data = $request;
            $user = auth('api')->user();
            $users = $this->user
                ->where('enterprise_id', $user->enterprise_id)
                ->where('id', '!=', $user->id);

            if ($data->has('name') && $data->get('name')) {
                $string = "%" . $data['name'] . "%";
                $users = $users->where('name', 'LIKE', $string);
            }
            if ($data->has('email') && $data->get('email')) {
                $string = "%" . $data['email'] . "%";
                $users = $users->where('email', 'LIKE', $string);
            }
            if ($data->has('type') && $data->get('type')) {
                $string = "%" . $data['type'] . "%";
                $users = $users->where('type', 'LIKE', $string);
            }

            return response()->json($users->orderBy('id')->get(), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function clerks()
    {
        try {
            $user = auth('api')->user();
            $users = $this->user
                ->where('enterprise_id', $user->enterprise_id)
                ->where('type', '!=', 'administrador')
                ->orderBy('id');

            return response()->json($users->get(), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function profile()
    {
        try {
            $user = auth('api')->user();

            return response()->json($user, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }
}
