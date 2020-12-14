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
    public $func;

    public function __construct()
    {
        $this->func = new functions();
    }

    // retorna a quantidade de leads total
    public function leadsTotal(Request $request, Lead $lead)
    {
        try {
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $data = $request->all();

            $user = auth('api')->user();

            $leads = $this->func->filter($data, $lead, $user);

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

            $leads = $this->func->filter($data, $lead, $user);
            $leads = $leads->where('status', '0');

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

            $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('follow_ups.type', 'n_vendido')
                ->where('leads.status', '3');
            $leads = $this->func->filter($data, $leads, $user);

            return response()->json($leads->distinct('follow_ups.lead_id')->get()->count(), 200);
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

            $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('follow_ups.type', 'vendido')
                ->where('leads.status', '2');
            $leads = $this->func->filter($data, $leads, $user);

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

            $leads = $this->func->filter($data, $lead, $user);
            $leads = $leads->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();
            $graphic = $this->func->graphic($leads);

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

            $leads = $this->func->filter($data, $lead, $user);
            $leads = $leads->where('status', '0')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();
            $graphic = $this->func->graphic($leads);

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

            $leads = $this->func->filter($data, $lead, $user);
            $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('follow_ups.type', 'n_vendido')
                ->where('leads.status', '3')
                ->select(DB::raw("DATE(follow_ups.created_at) as date"), DB::raw('COUNT(DISTINCT follow_ups.lead_id) as leads'))
                ->groupBy('date')
                ->get();
            $graphic = $this->func->graphic($leads);

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

            $leads = $this->func->filter($data, $lead, $user);
            $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('follow_ups.type', 'vendido')
                ->where('leads.status', '2')
                ->select(DB::raw('DATE(follow_ups.created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();
            $graphic = $this->func->graphic($leads);

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
            $rank = [];
            $users = User::where('enterprise_id', $enterprise)->where('type', '!=', 'administrador')->get();

            foreach ($users as $user) {
                $leads = Lead::where('user_id', $user->id)->get()->count();

                $sales = Lead::join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                    ->where('leads.user_id', $user->id)
                    ->where('follow_ups.type', 'vendido')
                    ->get()
                    ->count();

                array_push($rank, ['id' => $user->id, 'user' => $user->name, 'leads' => $leads, 'sales' => $sales, 'rate' => ($sales * 1.1) + ($leads * 0.1)]);
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
        try {
            $user = auth('api')->user();

            $rank = LEAD::where('enterprise_id', $user->enterprise_id)
                ->select('source', DB::raw('COUNT(source) as count'))
                ->groupBy('source')
                ->orderBy('count', 'DESC')
                ->get();

            return response()->json($rank, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }
}
