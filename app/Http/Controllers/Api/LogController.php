<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiMessages;
use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use App\Models\Lead;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function __construct()
    {
        $this->middleware('administrator')->except('store');
    }

    public function index()
    {
        try {
            $user = auth('api')->user();

            $leads = Lead::with('user')
                ->onlyTrashed()
                ->where('enterprise_id', $user->enterprise_id)
                ->get();

            return response()->json($leads, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function show($id)
    {
        try {
            $user = auth('api')->user();

            $leads = Lead::with('user')
                ->onlyTrashed()
                ->where('enterprise_id', $user->enterprise_id)
                ->find($id);

            return response()->json($leads, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = auth('api')->user();

            Lead::with('user')
                ->onlyTrashed()
                ->where('enterprise_id', $user->enterprise_id)
                ->find($id)
                ->restore();

            FollowUp::onlyTrashed()->join('leads', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('leads.enterprise_id', $user->enterprise_id)
                ->where('lead_id', $id)
                ->restore();

            return response()->json("Lead restore with success", 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function destroy($id)
    {
        try {
            $user = auth('api')->user();

            FollowUp::onlyTrashed()
                ->join('leads', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('leads.enterprise_id', $user->enterprise_id)
                ->where('lead_id', $id)
                ->forceDelete();

            Lead::onlyTrashed()
                ->where('enterprise_id', $user->enterprise_id)
                ->find($id)
                ->forceDelete();

            return response()->json("Lead deleted with success", 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function destroyAll()
    {
        try {
            $user = auth('api')->user();

            FollowUp::onlyTrashed()
                ->join('leads', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('leads.enterprise_id', $user->enterprise_id)
                ->forceDelete();

            Lead::onlyTrashed()
                ->where('enterprise_id', $user->enterprise_id)
                ->forceDelete();

            return response()->json("All Lead deleted with success", 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }
}
