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
        $user = auth('api')->user();
        $enterprise = $this->enterprise->findOrFail($user->enterprise_id);

        return response()->json([
            'enterprise' => $enterprise,
            'user' => $user
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        if (!$request->has('password') || !$request->get('password')) {
            $message = new ApiMessages('You need to have a password');

            return response()->json($message->getMessage(), 401);
        }

        try {
            $this->enterprise
                ->create($data)
                ->user()
                ->create(
                    [
                        'name' => $data['name_user'],
                        'email' => $data['email'],
                        'password' => bcrypt($data['password']),
                        'type' => "administrador"
                    ]
                );

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
        $data = $request->all();

        try {
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
