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
        $this->middleware('administrator')->only(['index', 'store', 'show', 'update', 'destroy']);
    }

    public function index()
    {
        try {
            $leads = Lead::onlyTrashed()->get();

            return response()->json($leads, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        try {
            Lead::onlyTrashed()->where('id', $id)->restore();
            FollowUp::onlyTrashed()->where('lead_id', $id)->restore();

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

            FollowUp::onlyTrashed()->where('lead_id', $id)->forceDelete();
            Lead::onlyTrashed()->where('enterprise_id', $user->enterprise_id)->where('id', $id)->forceDelete();

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
            // FollowUp::onlyTrashed()->where('enterprise_id', $user->enterprise_id)->forceDelete();
            // Lead::onlyTrashed()->where('enterprise_id', $user->enterprise_id)->forceDelete();

            // return response()->json("All Lead deleted with success", 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }
}
