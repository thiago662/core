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
            $data['user_id'] = auth('api')->user()->id;
            $lead = Lead::findOrFail($data['lead_id']);

            if ($data['created_at'] == "") {
                unset($data['created_at']);
            } else {
                $data['created_at'] = date(
                    "Y-m-d H:i:s",
                    strtotime(
                        date("H:i:s"),
                        strtotime($data['created_at'])
                    )
                );
            }

            if (isset($data['type']) && $data['type'] == "anotado") {
                unset($data['value']);
                $this->followUp->create($data);

                if ($lead->status == 0 || $lead->status == 3) {
                    $lead->update(['status' => 1]);
                }
            } else if (isset($data['type']) && $data['type'] == "adm_anotado") {
                unset($data['value']);
                $this->followUp->create($data);
            } else if (isset($data['type']) && $data['type'] == "vendido") {
                $data['message'] = "lead vendido";
                $this->followUp->create($data);

                $lead->update(['status' => 2]);
            } else if (isset($data['type']) && $data['type'] == "n_vendido") {
                unset($data['value']);
                $this->followUp->create($data);

                $lead->update(['status' => 3]);
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
            $lead = Lead::where('enterprise_id', $user->enterprise_id)->find($id);

            if (
                isset($lead) &&
                $user->enterprise_id == $lead->enterprise_id &&
                ($user->type == "administrador" || ($user->type == "atendente" &&
                    isset($lead) && $user->id == $lead->user_id))
            ) {
                $followUp = $this->followUp
                    ->with('user')
                    ->where('lead_id', $lead->id)
                    ->orderBy('id', 'DESC')
                    ->get();

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

    public function showId($id)
    {
        try {
            $user = auth('api')->user();
            $lead = Lead::where('enterprise_id', $user->enterprise_id)
                ->findOrFail($id);

            $followUps = FollowUp::where('lead_id', $lead->id)
                ->where('type', 'vendido');

            return response()->json($followUps->first(), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }
}
