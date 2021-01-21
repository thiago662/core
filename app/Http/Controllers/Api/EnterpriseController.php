<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiMessages;

use App\Http\Controllers\Controller;
use App\Models\Enterprise;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\http\Controllers\Api\Auth\AuthController;

class EnterpriseController extends Controller
{
    public function __construct()
    {
        $this->middleware('administrator');
    }

    public function index()
    {
        try {
            $user = auth('api')->user();
            $enterprise = Enterprise::findOrFail($user->enterprise_id);

            return response()->json($enterprise, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function store(Request $request)
    {
        try {
            Enterprise::create($request->all());

            return (new AuthController())->login($request);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function show($id)
    {
        try {
            $enterprise = Enterprise::findOrFail($id);

            return response()->json($enterprise, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request['name'] = Str::lower($request['name']);
            $request['address'] = Str::lower($request['address']);

            Enterprise::findOrFail($id)
                ->update($request->all());

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
            Enterprise::findOrFail($id)
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
