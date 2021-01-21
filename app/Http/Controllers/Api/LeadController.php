<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiMessages;

use App\Dictionaries\UserDictionary;
use App\Dictionaries\LeadDictionary;
use App\Dictionaries\FollowUpDictionary;
use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LeadController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        try {
            $user = auth('api')->user();

            $leads = Lead::with('user', 'followup')
                ->where('enterprise_id', $user->enterprise_id);

            if ($user->type == UserDictionary::CLERK) {
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
            $request['email'] = Str::lower($request['email']);
            $request['name'] = Str::lower($request['name']);
            $request['source'] = Str::lower($request['source']);

            $user = auth('api')->user();

            $request['enterprise_id'] = $user->enterprise_id;
            $request['status'] = LeadDictionary::CREATED;
            $request['type'] = FollowUpDictionary::CREATE;

            if (!$request->has('message') || !$request->get('message')) {
                $request['message'] = FollowUpDictionary::CREATED_LEAD;
            }
            if ($user->type == "atendente") {
                $request['user_id'] = $user->id;
            }

            $data = $request->only('type', 'message');
            $data['user_id'] = $user->id;

            Lead::create($request->all())
                ->followUp()
                ->create($data);

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
            $lead = Lead::with(['followUp', 'user'])
                ->where('enterprise_id', $user->enterprise_id)
                ->find($id);

            if (isset($lead) && $user->enterprise_id == $lead->enterprise_id && ($user->type == UserDictionary::ADMINISTRATOR || ($user->type == UserDictionary::CLERK && $user->id == $lead->user_id))) {
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
            $request['email'] = Str::lower($request['email']);
            $request['name'] = Str::lower($request['name']);
            $request['source'] = Str::lower($request['source']);

            Lead::findOrFail($id)
                ->update($request->all());

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

            Lead::findOrFail($id)
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

    public function filter(Request $request)
    {
        try {
            $user = auth('api')->user();

            $lead = Lead::with('user', 'followup')
                ->where('enterprise_id', $user->enterprise_id);

            if ($user->type == "atendente") {
                $lead = $lead->where('user_id', $user->id);
            }

            foreach ($request->only(['name', 'email', 'phone', 'source']) as $key => $value) {
                if ($value != '' || $value != null) {
                    $value = Str::lower($value);
                    $value = "%" . $value . "%";
                    $lead = $lead->where($key, 'LIKE', $value);
                }
            }

            foreach ($request->only(['status', 'user_id']) as $key => $value) {
                if ($value != '' || $value != null) {
                    $lead = $lead->where($key, $value);
                }
            }

            return response()->json($lead->orderBy('status')->get(), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }
}
