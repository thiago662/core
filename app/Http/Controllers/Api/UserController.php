<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Api\ApiMessages;
use App\Http\Requests\UserRequest;
use App\Models\Lead;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->middleware('administrator')->only(['index', 'store', 'destroy', 'filter']);
    }

    public function index()
    {
        $enterprise_id = auth('api')->user()->enterprise_id;
        $users = $this->user->where('enterprise_id', $enterprise_id)->orderBy('id')->get();

        return response()->json($users, 200);
    }

    public function store(UserRequest $request)
    {
        $data = $request->all();

        if (!$request->has('password') || !$request->get('password')) {
            $message = new ApiMessages('You need to have a password');

            return response()->json($message->getMessage(), 401);
        }

        try {
            $data['password'] = bcrypt($data['password']);
            $enterprise_id = auth('api')->user()->enterprise_id;
            $data['enterprise_id'] = $enterprise_id;
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
            $enterprise_id = auth('api')->user()->enterprise_id;
            $user = $this->user->where('enterprise_id', $enterprise_id)->findOrFail($id);

            return response()->json($user, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        Validator::make($data, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id . ',id',
            'type' => 'required'
        ])->validate();

        if ($request->has('password') && $request->get('password')) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        try {
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
            Lead::where('user_id', $id)->delete();

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
            $data = $request;
            $proc = "";
            $users = $this->user;

            if (isset($data['name']) && $data['name'] != '') {
                $proc = "%" . $data['name'] . "%";
                $users = $users->where('name', 'LIKE', $proc);
            }

            if (isset($data['email']) && $data['email'] != '') {
                $proc = "%" . $data['email'] . "%";
                $users = $users->where('email', 'LIKE', $proc);
            }

            if (isset($data['type']) && $data['type'] != '') {
                $proc = "%" . $data['type'] . "%";
                $users = $users->where('type', 'LIKE', $proc);
            }

            return response()->json($users->orderBy('id')->get(), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function clearks()
    {
        $enterprise_id = auth('api')->user()->enterprise_id;
        $users = $this->user->where('enterprise_id', $enterprise_id)->where('id', '!=', auth('api')->user()->id)->orderBy('id')->get();

        return response()->json($users, 200);
    }
}
