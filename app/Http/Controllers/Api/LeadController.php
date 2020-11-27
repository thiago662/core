<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiMessages;
use App\Http\Controllers\Controller;
use App\Models\FollowUp;
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
    }

    public function index()
    {
        $user = auth('api')->user();

        $leads = $this->lead->with('user')->where('enterprise_id', $user->enterprise_id);

        if ($user->type == "atendente") {
            $leads = $leads->where('user_id', $user->id);
        }

        return response()->json($leads->orderBy('status')->get(), 200);
    }

    public function store(Request $request)
    {
        try {
            $request['name'] = mb_strtolower($request['name'], 'UTF-8');
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $request['contact'] = mb_strtolower($request['contact'], 'UTF-8');
            $data = $request->all();

            $user = auth('api')->user();

            $data['enterprise_id'] = $user->enterprise_id;
            $data['status'] = "0";
            $data['type'] = "criado";

            if ($user->type == "atendente") {
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
            $user = auth('api')->user();
            $lead = $this->lead
                ->with(['followUp', 'user'])
                ->where('enterprise_id', $user->enterprise_id)
                ->findOrFail($id);

            if ($user->type == "administrador") {
                return response()->json($lead, 200);
            } else if ($user->type == "atendente" && $user->id == $lead->user_id) {
                return response()->json($lead, 200);
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
            FollowUp::where('lead_id', $id)->delete();

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
            $leads = $this->lead->with('user');
            $user = auth('api')->user();

            if ($user->type == "atendente") {
                $leads = $leads->where('user_id', $user->id);
            }
            if (isset($data['name']) && $data['name'] != '') {
                $string = "%" . $data['name'] . "%";
                $leads = $leads->where('name', 'LIKE', $string);
            }
            if (isset($data['contact']) && $data['contact'] != '') {
                $string = "%" . $data['contact'] . "%";
                $leads = $leads->where('contact', 'LIKE', $string);
            }
            if (isset($data['source']) && $data['source'] != '') {
                $string = "%" . $data['source'] . "%";
                $leads = $leads->where('source', 'LIKE', $string);
            }
            if (isset($data['user_id']) && $data['user_id'] != '') {
                $leads = $leads->where('user_id', $data['user_id']);
            }

            return response()->json($leads->orderBy('status')->get(), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }
}
