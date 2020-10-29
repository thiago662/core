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
        // $this->middleware('administrator')->only('index');
    }

    public function index()
    {
        $enterprise_id = auth('api')->user()->enterprise_id;
        $user_id = auth('api')->user()->id;
        $user_type = auth('api')->user()->type;

        if ($user_type == "administrador") {
            $leads = $this->lead->with('user')->where('enterprise_id', $enterprise_id)->orderBy('id')->get();
        } else {
            $leads = $this->lead->with('user')->where('enterprise_id', $enterprise_id)->where('user_id', $user_id)->orderBy('id')->get();
        }

        // $leads = $this->lead->with('user')->where('enterprise_id', $enterprise_id)->orderBy('id')->get();

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

    //filtro
    public function filter(Request $request)
    {
        try {
            $data = $request;
            $proc = "";
            $leads = $this->lead;

            if (isset($data['name']) && $data['name'] != '') {
                $proc = "%" . $data['name'] . "%";
                $leads = $leads->where('name', 'LIKE', $proc);
            }

            if (isset($data['contact']) && $data['contact'] != '') {
                $proc = "%" . $data['contact'] . "%";
                $leads = $leads->where('contact', 'LIKE', $proc);
            }

            if (isset($data['type']) && $data['type'] != '') {
                $proc = "%" . $data['type'] . "%";
                $leads = $leads->where('type', 'LIKE', $proc);
            }

            if (isset($data['source']) && $data['source'] != '') {
                $proc = "%" . $data['source'] . "%";
                $leads = $leads->where('source', 'LIKE', $proc);
            }

            return response()->json($leads->get(), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }
}
