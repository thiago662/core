<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiMessages;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    private $lead;

    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
        // $this->middleware('administrator')->only('index');
    }

    public function index()
    {
        $enterprise_id = auth('api')->user()->enterprise_id;
        $user_id = auth('api')->user()->id;
        $user_type = auth('api')->user()->type;

        if ($user_type == "administrador") {
            $leads = $this->lead->with('user')->where('enterprise_id', $enterprise_id)->orderBy('status')->get();
        } else {
            $leads = $this->lead->with('user')->where('enterprise_id', $enterprise_id)->where('user_id', $user_id)->orderBy('status')->get();
        }


        return response()->json($leads, 200);
    }

    public function store(Request $request)
    {
        try {
            $request['name'] = mb_strtolower($request['name'], 'UTF-8');
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $request['contact'] = mb_strtolower($request['contact'], 'UTF-8');
            $data = $request->all();

            $user = auth('api')->user();

            if ($user->type == "atendente") {
                $data['enterprise_id'] = $user->enterprise_id;
                $data['status'] = "0";
                $data['type'] = "criado";
                $data['user_id'] = $user->id;

                $this->lead
                    ->create($data)
                    ->followUp()
                    ->create(
                        [
                            'type' => $data['type'],
                            'message' => "lead criado"
                        ]
                    );
            } else if ($user->type == "administrador") {
                $data['enterprise_id'] = $user->enterprise_id;
                $data['status'] = "0";
                $data['type'] = "criado";

                $this->lead
                    ->create($data)
                    ->followUp()
                    ->create(
                        [
                            'type' => $data['type'],
                            'message' => "lead criado"
                        ]
                    );
            }

            return response()->json([
                'data' => [
                    'msg' => 'Lead created with success'
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
            $lead = $this->lead->with(['followUp', 'user'])->where('enterprise_id', $enterprise_id)->findOrFail($id);

            return response()->json($lead, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request['name'] = mb_strtolower($request['name'], 'UTF-8');
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $request['contact'] = mb_strtolower($request['contact'], 'UTF-8');
            $data = $request->all();

            $this->lead
                ->findOrFail($id)
                ->update($data);

            return response()->json([
                'data' => [
                    'msg' => 'Lead updated with success'
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
            $this->lead
                ->findOrFail($id)
                ->delete();

            return response()->json([
                'data' => [
                    'msg' => 'Lead deleted with success'
                ]
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    //filtro
    public function filter(Request $request)
    {
        try {
            $request['name'] = mb_strtolower($request['name'], 'UTF-8');
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $request['contact'] = mb_strtolower($request['contact'], 'UTF-8');
            $data = $request->all();
            $proc = "";
            $leads = $this->lead->with('user');

            if (isset($data['name']) && $data['name'] != '') {
                $proc = "%" . $data['name'] . "%";
                $leads = $leads->where('name', 'LIKE', $proc);
            }

            if (isset($data['contact']) && $data['contact'] != '') {
                $proc = "%" . $data['contact'] . "%";
                $leads = $leads->where('contact', 'LIKE', $proc);
            }

            if (isset($data['source']) && $data['source'] != '') {
                $proc = "%" . $data['source'] . "%";
                $leads = $leads->where('source', 'LIKE', $proc);
            }

            if (isset($data['user_id']) && $data['user_id'] != '') {
                $leads = $leads->where('user_id', $data['user_id']);
                // $leads = $leads->whereIn('user_id', $data['user_id']);
            }

            return response()->json($leads->orderBy('id')->get(), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }
}
