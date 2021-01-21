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

class FollowUpController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        try {
            $followUps = FollowUp::all();

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
                FollowUp::create($data);

                if ($lead->status == 0 || $lead->status == 3) {
                    $lead->update(['status' => 1]);
                }
            } else if (isset($data['type']) && $data['type'] == "adm_anotado") {
                unset($data['value']);
                FollowUp::create($data);
            } else if (isset($data['type']) && $data['type'] == "vendido") {
                if ($lead->status == '2') {
                    return response()->json('Lead already saled', 401);
                } else {
                    $data['message'] = "lead vendido";
                    FollowUp::create($data);

                    $lead->update(['status' => 2]);
                }
            } else if (isset($data['type']) && $data['type'] == "n_vendido") {
                if ($lead->status == '2') {
                    return response()->json('Lead already saled', 401);
                } else {
                    unset($data['value']);
                    $data['message'] = "lead não vendido";

                    FollowUp::create($data);

                    $lead->update(['status' => 3]);
                }
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

    // public function store(Request $request)
    // {
    //     try {
    //         $request['user_id'] = auth('api')->user()->id;
    //         $lead = Lead::findOrFail($request['lead_id']);

    //         if ($request->get('created_at') == '') {
    //             unset($request['created_at']);
    //         } else {
    //             $request['created_at'] = date(
    //                 "Y-m-d H:i:s",
    //                 strtotime(
    //                     date("H:i:s"),
    //                     strtotime($request['created_at'])
    //                 )
    //             );
    //         }

    //         if ($request->has('type') == FollowUpDictionary::NOTE) {
    //             FollowUp::create($request->all(
    //                 'type',
    //                 'message',
    //                 'user_id',
    //                 'lead_id'
    //             ));

    //             if ($lead->status == LeadDictionary::CREATED || $lead->status == LeadDictionary::NOT_SOLD) {
    //                 $lead->update(['status' => LeadDictionary::PROGRESS]);
    //             }
    //         } else if ($request->has('type') == FollowUpDictionary::MANAGER_NOTE) {
    //             FollowUp::create($request->all(
    //                 'type',
    //                 'message',
    //                 'user_id',
    //                 'lead_id'
    //             ));
    //         } else if ($request->has('type') == FollowUpDictionary::SOLD) {
    //             if ($lead->status == LeadDictionary::SOLD) {
    //                 return response()->json('Lead already saled', 401);
    //             } else {
    //                 $request['message'] = FollowUpDictionary::SOLD_LEAD;
    //                 FollowUp::create($request->all(
    //                     'type',
    //                     'message',
    //                     'user_id',
    //                     'lead_id',
    //                     'value',
    //                     'created_at'
    //                 ));

    //                 $lead->update(['status' => LeadDictionary::SOLD]);
    //             }
    //         } else if ($request->has('type') == FollowUpDictionary::NOT_SOLD) {
    //             if ($lead->status == LeadDictionary::SOLD) {
    //                 return response()->json('Lead already saled', 401);
    //             } else {
    //                 $request['message'] = FollowUpDictionary::NOT_SOLD_LEAD;
    //                 FollowUp::create($request->all(
    //                     'type',
    //                     'message',
    //                     'user_id',
    //                     'lead_id',
    //                     'reason',
    //                     'created_at'
    //                 ));

    //                 $lead->update(['status' => LeadDictionary::NOT_SOLD]);
    //             }
    //         }

    //         return response()->json([
    //             'data' => [
    //                 'msg' => 'FollowUp created with success'
    //             ]
    //         ], 201);
    //     } catch (\Exception $e) {
    //         $message = new ApiMessages($e->getMessage());

    //         return response()->json($message->getMessage(), 401);
    //     }
    // }

    public function show($id)
    {
        try {
            $user = auth('api')->user();
            $lead = Lead::where('enterprise_id', $user->enterprise_id)->find($id);

            if (isset($lead) && $user->enterprise_id == $lead->enterprise_id && ($user->type == "administrador" || ($user->type == "atendente" && $user->id == $lead->user_id))) {
                $followUp = FollowUp::with('user')
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
            FollowUp::findOrFail($id)
                ->update($request->all());

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
            FollowUp::findOrFail($id)
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
                ->find($id);

            if (isset($lead->status) && $lead->status == 2) {
                $followUps = FollowUp::where('lead_id', $lead->id)
                    ->where('type', 'vendido');

                return response()->json($followUps->first(), 200);
            }
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }
}
