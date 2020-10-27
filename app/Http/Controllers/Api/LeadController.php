<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiMessages;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    private $lead;

    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
        $this->middleware('administrator')->only('index');
    }

    public function index()
    {
        $enterprise_id = auth('api')->user()->enterprise_id;
        $leads = $this->lead->with('user')->where('enterprise_id', $enterprise_id)->orderBy('id')->get();

        return response()->json($leads, 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        try {
            $enterprise_id = auth('api')->user()->enterprise_id;
            $data['enterprise_id'] = $enterprise_id;
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

            return response()->json([
                'data' => [
                    'msg' => 'Lead created with success'
                ]
            ]);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function show($id)
    {
        try {
            $enterprise_id = auth('api')->user()->enterprise_id;
            $lead = $this->lead->with(['followUp','user'])->where('enterprise_id', $enterprise_id)->findOrFail($id);

            return response()->json($lead, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        try {
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
}
