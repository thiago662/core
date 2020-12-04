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

        try {
            $user = auth('api')->user();
    
            $leads = $this->lead->with('user')->where('enterprise_id', $user->enterprise_id);
    
            if ($user->type == "atendente") {
                $leads = $leads->where('user_id', $user->id);
            }
    
            return response()->json($leads->orderBy('status')->get(), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function store(Request $request)
    {
        try {
            $request['name'] = mb_strtolower($request['name'], 'UTF-8');
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $request['phone'] = mb_strtolower($request['phone'], 'UTF-8');
            $request['email'] = mb_strtolower($request['email'], 'UTF-8');
            $data = $request->all();

            $user = auth('api')->user();

            $data['enterprise_id'] = $user->enterprise_id;
            $data['status'] = "0";
            $data['type'] = "criado";

            if (!isset($data['message']) || $data['message'] == '') {
                $data['message'] = "Lead criado";
            }

            if ($user->type == "atendente") {
                $data['user_id'] = $user->id;

                $this->lead
                    ->create($data)
                    ->followUp()
                    ->create(
                        [
                            'type' => $data['type'],
                            'message' => $data['message'],
                            'user_id' => $user->id
                        ]
                    );
            } else if ($user->type == "administrador") {
                $this->lead
                    ->create($data)
                    ->followUp()
                    ->create(
                        [
                            'type' => $data['type'],
                            'message' => $data['message'],
                            'user_id' => $user->id
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
            $request['phone'] = mb_strtolower($request['phone'], 'UTF-8');
            $request['email'] = mb_strtolower($request['email'], 'UTF-8');
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
            $request['phone'] = mb_strtolower($request['phone'], 'UTF-8');
            $request['email'] = mb_strtolower($request['email'], 'UTF-8');
            $data = $request->all();
            $user = auth('api')->user();
            $leads = $this->lead->with('user')->where('enterprise_id', $user->enterprise_id);

            if ($user->type == "atendente") {
                $leads = $leads->where('user_id', $user->id);
            }
            if (isset($data['name']) && $data['name'] != '') {
                $string = "%" . $data['name'] . "%";
                $leads = $leads->where('name', 'LIKE', $string);
            }
            if (isset($data['phone']) && $data['phone'] != '') {
                $string = "%" . $data['phone'] . "%";
                $leads = $leads->where('phone', 'LIKE', $string);
            }
            if (isset($data['email']) && $data['email'] != '') {
                $string = "%" . $data['email'] . "%";
                $leads = $leads->where('email', 'LIKE', $string);
            }
            if (isset($data['source']) && $data['source'] != '') {
                $string = "%" . $data['source'] . "%";
                $leads = $leads->where('source', 'LIKE', $string);
            }
            if (isset($data['status']) && $data['status'] != '') {
                $leads = $leads->where('status', $data['status']);
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
