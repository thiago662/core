<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiMessages;
use App\Http\Controllers\Controller;
use App\Models\Enterprise;
use Illuminate\Http\Request;
use App\http\Controllers\Api\Auth\AuthController;

class EnterpriseController extends Controller
{
    private $enterprise;

    public function __construct(Enterprise $enterprise)
    {
        $this->enterprise = $enterprise;
        $this->middleware('administrator');
    }

    public function index()
    {
        try {
            $user = auth('api')->user();
            $enterprise = $this->enterprise->findOrFail($user->enterprise_id);

            return response()->json($enterprise, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function store(Request $request)
    {
        try {
            $request['name'] = mb_strtolower($request['name'], 'UTF-8');
            $data = $request->all();

            if (!$request->has('password') || !$request->get('password')) {
                $message = new ApiMessages('You need to have a password');

                return response()->json($message->getMessage(), 401);
            }

            if ($request['password'] == $request['password_confirmation']) {
                $this->enterprise
                    ->create($data)
                    ->user()
                    ->create(
                        [
                            'name' => mb_strtolower($data['name_user'], 'UTF-8'),
                            'email' => mb_strtolower($data['email'], 'UTF-8'),
                            'password' => bcrypt($data['password']),
                            'type' => "administrador"
                        ]
                    );
            }

            return (new AuthController())->login($request);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function show($id)
    {
        try {
            $enterprise = $this->enterprise->findOrFail($id);

            return response()->json($enterprise, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request['name'] = mb_strtolower($request['name'], 'UTF-8');
            $request['address'] = mb_strtolower($request['address'], 'UTF-8');
            $data = $request->all();

            $this->enterprise
                ->findOrFail($id)
                ->update($data);

            return response()->json([
                'data' => [
                    'msg' => 'Enterprise updated with success'
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
            $this->enterprise
                ->findOrFail($id)
                ->delete();

            return response()->json([
                'data' => [
                    'msg' => 'Enterprise deleted with success'
                ]
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }
}
