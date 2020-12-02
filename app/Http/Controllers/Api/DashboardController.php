<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiMessages;
use App\Api\functions;
use App\Models\Lead;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // retorna a quantidade de leads total
    public function leadsTotal(Request $request, Lead $lead)
    {
        try {
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $data = $request->all();

            $user = auth('api')->user();
            $func = new functions();

            $leads = $func->filter($data, $lead, $user->enterprise_id);
            $leads = $func->authorization($user, $leads);

            return response()->json($leads->get()->count(), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    // retorna todos so leads
    public function leadsOpen(Request $request, Lead $lead)
    {
        try {
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $data = $request->all();

            $user = auth('api')->user();
            $func = new functions();

            $leads = $func->filter($data, $lead, $user->enterprise_id);
            $leads = $leads->where('status', '0');
            $leads = $func->authorization($user, $leads);

            return response()->json($leads->get()->count(), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    // retorna todos os leads
    public function leadsClose(Request $request, Lead $leads)
    {
        try {
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $data = $request->all();

            $user = auth('api')->user();
            $func = new functions();

            $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('follow_ups.type', 'n_vendido')
                ->where('leads.status', '3');
            $leads = $func->authorization($user, $leads);
            $leads = $func->filter($data, $leads, $user->enterprise_id);

            return response()->json($leads->get()->count(), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    // retorna todos so leads
    public function leadsSales(Request $request, Lead $leads)
    {
        try {
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $data = $request->all();

            $user = auth('api')->user();
            $func = new functions();

            $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('follow_ups.type', 'vendido')
                ->where('leads.status', '2');
            $leads = $func->authorization($user, $leads);
            $leads = $func->filter($data, $leads, $user->enterprise_id);

            return response()->json($leads->get()->count(), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    // retorna todos so leads
    public function graphicLead(Request $request, Lead $lead)
    {
        try {
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $data = $request->all();

            $user = auth('api')->user();
            $func = new functions();

            $leads = $func->filter($data, $lead, $user->enterprise_id);
            $leads = $leads->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();
            $graphic = $func->graphic($leads);

            return response()->json($graphic, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    // retorna todos so leads
    public function graphicOpen(Request $request, Lead $lead)
    {
        try {
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $data = $request->all();

            $user = auth('api')->user();
            $func = new functions();

            $leads = $func->filter($data, $lead, $user->enterprise_id);
            $leads = $leads->where('status', '0')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();
            $graphic = $func->graphic($leads);

            return response()->json($graphic, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    // retorna todos so leads
    public function graphicClose(Request $request, Lead $lead)
    {
        try {
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $data = $request->all();

            $user = auth('api')->user();
            $func = new functions();

            $leads = $func->filter($data, $lead, $user->enterprise_id);
            $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('follow_ups.type', 'n_vendido')
                ->where('leads.status', '3')
                ->select(DB::raw('DATE(follow_ups.created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();
            $graphic = $func->graphic($leads);

            return response()->json($graphic, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    // retorna todos so leads
    public function graphicSale(Request $request, Lead $lead)
    {
        try {
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $data = $request->all();

            $user = auth('api')->user();
            $func = new functions();

            $leads = $func->filter($data, $lead, $user->enterprise_id);
            $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('follow_ups.type', 'vendido')
                ->where('leads.status', '2')
                ->select(DB::raw('DATE(follow_ups.created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();
            $graphic = $func->graphic($leads);

            return response()->json($graphic, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    // retorna os ranking com o desempenho dos atendentes
    public function rankingLead()
    {
        try {
            $enterprise = auth('api')->user()->enterprise_id;
            $rank = array();
            $users = User::where('enterprise_id', $enterprise)->where('type', '!=', 'administrador')->get();

            foreach ($users as $user) {
                $leads = Lead::where('user_id', $user->id)->get()->count();

                $sales = Lead::join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                    ->where('leads.user_id', $user->id)
                    ->where('follow_ups.type', 'vendido')
                    ->get()
                    ->count();

                array_push($rank, array('id' => $user->id, 'user' => $user->name, 'leads' => $leads, 'sales' => $sales));
            }
            $help = array_column($rank, 'sales');
            array_multisort($help, SORT_DESC, $rank);

            return response()->json($rank, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    // retorna os ranking com o desempenho dos atendentes
    public function rankingSource()
    {
        $user = auth('api')->user();
        $rank = array();
        $leads = LEAD::where('enterprise_id', $user->enterprise_id)
            ->select('source')
            ->groupBy('source')
            ->get();

        foreach ($leads as $lead) {
            $count = LEAD::where('enterprise_id', $user->enterprise_id)
            ->where('source', $lead->source)
            ->get()
            ->count();

            array_push($rank, array('source' => $lead->source, 'count' => $count));
        }

        return $rank;
    }
}
