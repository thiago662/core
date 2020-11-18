<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiMessages;
use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use App\Models\Lead;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    private $followUp;

    public function __construct(FollowUp $followUp)
    {
        $this->followUp = $followUp;
    }

    public function index()
    {
        try {
            $followUps = $this->followUp->all();

            return response()->json($followUps, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();

            if (isset($data['type']) && $data['type'] == "anotado") {
                unset($data['created_at']);
                unset($data['value']);
                $this->followUp->create($data);

                $lead = Lead::find($data['lead_id']);
                if ($lead->status == 0) {
                    $lead->status = 1;
                }
                $lead->save();
            } else if (isset($data['type']) && $data['type'] == "vendido") {
                if ($data['created_at'] == "") {
                    unset($data['created_at']);
                } else {
                    $data['created_at'] = date("Y-m-d H:i:s", strtotime('+12 hour', strtotime($data['created_at'])));
                }
                $data['message'] = "lead vendido";
                $this->followUp->create($data);

                $lead = Lead::find($data['lead_id']);
                $lead->status = 2;
                $lead->save();
            } else if (isset($data['type']) && $data['type'] == "n_vendido") {
                unset($data['created_at']);
                unset($data['value']);
                $this->followUp->create($data);

                $lead = Lead::find($data['lead_id']);
                $lead->status = 3;
                $lead->save();
            }

            return response()->json([
                'data' => [
                    'msg' => 'FollowUp created with success'
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
            $lead = Lead::where('enterprise_id', $user->enterprise_id)->findOrFail($id);

            $followUp = $this->followUp->where('lead_id', $lead->id)->orderBy('id', 'DESC')->get();
            
            if ($user->type == "administrador") {
                return response()->json($followUp, 200);
            } else if ($user->type == "atendente" && $user->id == $lead->user_id) {
                return response()->json($followUp, 200);
            }
            
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();

            $this->followUp
                ->findOrFail($id)
                ->update($data);

            return response()->json([
                'data' => [
                    'msg' => 'FollowUp updated with success'
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
            $this->followUp
                ->findOrFail($id)
                ->delete();

            return response()->json([
                'data' => [
                    'msg' => 'FollowUp deleted with success'
                ]
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }
}
