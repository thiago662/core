<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiMessages;
use App\Dictionaries\LeadDictionary;
use App\Dictionaries\UserDictionary;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Lead;
use App\Models\User;

class UserController extends Controller
{
    public function __construct()
    {
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
            $users = User::where('enterprise_id', $user->enterprise_id)
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
            $request['email'] = Str::lower($request['email']);
            $request['name'] = Str::lower($request['name']);

            $user = auth('api')->user();

            $data = $request->all();
            $data['password'] = bcrypt($data['password']);
            $data['enterprise_id'] = $user->enterprise_id;

            User::create($data);

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

            if ($user->type == UserDictionary::ADMINISTRATOR) {
                $clerk = User::where('enterprise_id', $user->enterprise_id)
                    ->find($id);

                if ($clerk != []) {
                    return response()->json($clerk, 200);
                }
            }
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function update(UserUpdateRequest $request, $id)
    {
        try {
            $request['email'] = Str::lower($request['email']);
            $request['name'] = Str::lower($request['name']);

            $data = $request->all();

            if (!$request->has('password')) {
                unset($data['password']);
            } else {
                $data['password'] = bcrypt($data['password']);
            }

            User::findOrFail($id)
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
            User::findOrFail($id)
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
                ->whereIn('status', [LeadDictionary::CREATED, LeadDictionary::PROGRESS])
                ->update(['user_id' => $request['id']]);

            User::findOrFail($id)
                ->update(['email' => null]);

            User::findOrFail($id)
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
            $user = auth('api')->user();
            $users = User::where('enterprise_id', $user->enterprise_id)
                ->where('id', '!=', $user->id);

            foreach ($request->only(['name', 'email', 'type']) as $key => $value) {
                if ($value != '' || $value != null) {
                    $value = Str::lower($value);
                    $value = "%" . $value . "%";
                    $users = $users->where($key, 'LIKE', $value);
                }
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
            $users = User::where('enterprise_id', $user->enterprise_id)
                ->where('type', '!=', UserDictionary::ADMINISTRATOR)
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
