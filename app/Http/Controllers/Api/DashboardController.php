<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiMessages;

use App\Dictionaries\UserDictionary;
use App\Dictionaries\LeadDictionary;
use App\Dictionaries\FollowUpDictionary;
use App\Models\Lead;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function leadsTotal(Request $request)
    {
        try {
            $user = auth('api')->user();
            if ($user->type == UserDictionary::CLERK) {
                $lead = Lead::where('enterprise_id', $user->enterprise_id)
                    ->where('user_id', $user->id);
            } else {
                $lead = Lead::where('enterprise_id', $user->enterprise_id);
            }
            $lead = (new Lead)->filterDashboard($request, $lead);

            return response()->json($lead->count(), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function leadsOpen(Request $request)
    {
        try {
            $user = auth('api')->user();
            if ($user->type == UserDictionary::CLERK) {
                $lead = Lead::where('enterprise_id', $user->enterprise_id)
                    ->where('user_id', $user->id)
                    ->where('status', LeadDictionary::CREATED);
            } else {
                $lead = Lead::where('enterprise_id', $user->enterprise_id)
                    ->where('status', LeadDictionary::CREATED);
            }
            $lead = (new Lead)->filterDashboard($request, $lead);

            return response()->json($lead->count(), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function leadsClose(Request $request)
    {
        try {
            $user = auth('api')->user();
            if ($user->type == UserDictionary::CLERK) {
                $lead = Lead::where('enterprise_id', $user->enterprise_id)
                    ->where('leads.user_id', $user->id)
                    ->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                    ->where('follow_ups.type', FollowUpDictionary::NOT_SOLD)
                    ->where('leads.status', LeadDictionary::NOT_SOLD);
            } else {
                $lead = Lead::where('enterprise_id', $user->enterprise_id)
                    ->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                    ->where('follow_ups.type', FollowUpDictionary::NOT_SOLD)
                    ->where('leads.status', LeadDictionary::NOT_SOLD);
            }
            $lead = (new Lead)->filterDashboard($request, $lead);

            return response()->json($lead->distinct('follow_ups.lead_id')->count(), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function leadsSales(Request $request)
    {
        try {
            $user = auth('api')->user();
            if ($user->type == UserDictionary::CLERK) {
                $lead = Lead::where('enterprise_id', $user->enterprise_id)
                    ->where('leads.user_id', $user->id)
                    ->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                    ->where('follow_ups.type', FollowUpDictionary::SOLD)
                    ->where('leads.status', LeadDictionary::SOLD);
            } else {
                $lead = Lead::where('enterprise_id', $user->enterprise_id)
                    ->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                    ->where('follow_ups.type', FollowUpDictionary::SOLD)
                    ->where('leads.status', LeadDictionary::SOLD);
            }
            $lead = (new Lead)->filterDashboard($request, $lead);

            return response()->json($lead->count(), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function graphicLead(Request $request)
    {
        try {
            $user = auth('api')->user();
            if ($user->type == UserDictionary::CLERK) {
                $lead = Lead::where('enterprise_id', $user->enterprise_id)
                    ->where('user_id', $user->id);
            } else {
                $lead = Lead::where('enterprise_id', $user->enterprise_id);
            }
            $lead = (new Lead)->filterDashboard($request, $lead);
            $lead = $lead->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();
            $lead = (new Lead)->ConstructChart($lead);

            return response()->json($lead, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function graphicOpen(Request $request)
    {
        try {
            $user = auth('api')->user();
            if ($user->type == UserDictionary::CLERK) {
                $lead = Lead::where('enterprise_id', $user->enterprise_id)
                    ->where('user_id', $user->id)
                    ->where('status', LeadDictionary::CREATED);
            } else {
                $lead = Lead::where('enterprise_id', $user->enterprise_id)
                    ->where('status', LeadDictionary::CREATED);
            }
            $lead = (new Lead)->filterDashboard($request, $lead);
            $lead = $lead->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();
            $lead = (new Lead)->ConstructChart($lead);

            return response()->json($lead, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function graphicClose(Request $request)
    {
        try {
            $user = auth('api')->user();
            if ($user->type == UserDictionary::CLERK) {
                $lead = Lead::where('enterprise_id', $user->enterprise_id)
                    ->where('leads.user_id', $user->id)
                    ->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                    ->where('follow_ups.type', FollowUpDictionary::NOT_SOLD)
                    ->where('leads.status', LeadDictionary::NOT_SOLD);
            } else {
                $lead = Lead::where('enterprise_id', $user->enterprise_id)
                    ->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                    ->where('follow_ups.type', FollowUpDictionary::NOT_SOLD)
                    ->where('leads.status', LeadDictionary::NOT_SOLD);
            }
            $lead = (new Lead)->filterDashboard($request, $lead);
            $lead = $lead->select(DB::raw("DATE(follow_ups.created_at) as date"), DB::raw('COUNT(DISTINCT follow_ups.lead_id) as leads'))
                ->groupBy('date')
                ->get();
            $lead = (new Lead)->ConstructChart($lead);

            return response()->json($lead, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function graphicSale(Request $request)
    {
        try {
            $user = auth('api')->user();
            if ($user->type == UserDictionary::CLERK) {
                $lead = Lead::where('enterprise_id', $user->enterprise_id)
                    ->where('leads.user_id', $user->id)
                    ->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                    ->where('follow_ups.type', FollowUpDictionary::SOLD)
                    ->where('leads.status', LeadDictionary::SOLD);
            } else {
                $lead = Lead::where('enterprise_id', $user->enterprise_id)
                    ->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                    ->where('follow_ups.type', FollowUpDictionary::SOLD)
                    ->where('leads.status', LeadDictionary::SOLD);
            }
            $lead = (new Lead)->filterDashboard($request, $lead);
            $lead = $lead->select(DB::raw('DATE(follow_ups.created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();
            $lead = (new Lead)->ConstructChart($lead);

            return response()->json($lead, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function rankingLead()
    {
        try {
            $user = auth('api')->user();

            $lead = Lead::join('users', 'leads.user_id', '=', 'users.id')
                ->where('users.enterprise_id', $user->enterprise_id)
                ->where('users.type', '!=', UserDictionary::ADMINISTRATOR)
                ->where('users.deleted_at', null)
                ->select(
                    'users.id as id',
                    'users.name as user',
                    DB::raw('COUNT(leads) as leads'),
                    DB::raw('SUM(CASE WHEN leads.status = \'' . LeadDictionary::SOLD . '\' THEN 1 ELSE 0 END) as sales'),
                    DB::raw('SUM(CASE WHEN leads.status = \'' . LeadDictionary::SOLD . '\' THEN 1 ELSE 0 END) * 100 / COUNT(leads) as rate')
                )
                ->groupBy('users.id')
                ->orderBy('rate', 'desc')
                ->get();

            return response()->json($lead, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function rankingSource()
    {
        try {
            $user = auth('api')->user();

            $source = Lead::where('enterprise_id', $user->enterprise_id)
                ->select('source', DB::raw('COUNT(source) as count'))
                ->groupBy('source')
                ->orderBy('count', 'desc')
                ->get();

            return response()->json($source, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }
}
