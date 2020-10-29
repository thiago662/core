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
        $followUps = $this->followUp->all();

        return response()->json($followUps, 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        try {
            $this->followUp->create($data);
            $lead = Lead::find($data['lead_id']);
            $lead->status = 1;
            $lead->save();

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
            $followUp = $this->followUp->where('lead_id', $id)->orderBy('id','DESC')->get();

            return response()->json($followUp, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        try {
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
