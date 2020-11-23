<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiMessages;
use App\Models\Lead;
use App\Models\User;
use App\Models\FollowUp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
    }

    // retorna a quantidade de leads total
    public function leadsTotal(Request $request, Lead $lead)
    {
        try {
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $user = auth('api')->user();
            $data = $request;
            $leads = $lead->where('enterprise_id', $user->enterprise_id);

            if (isset($data['user_id']) && $data['user_id'] != '') {
                $leads = $leads->where('user_id', $data['user_id']);
            }

            if (isset($data['year']) && $data['year'] != '') {
                $leads = $leads->whereYear('created_at', $data['year']);
            }

            if (isset($data['month']) && $data['month'] != '') {
                $leads = $leads->whereMonth('created_at', $data['month']);
            }

            if (isset($data['source']) && $data['source'] != '') {
                $string = "%" . $data['source'] . "%";
                $leads = $leads->where('source', 'LIKE', $string);
            }

            if ($user->type == 'atendente') {
                $leads = count($leads->where('user_id', $user->id)->get());
            } else if ($user->type == 'administrador') {
                $leads = count($leads->get());
            }

            return response()->json($leads, 200);
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
            $user = auth('api')->user();
            $data = $request;
            $leads = $lead->where('enterprise_id', $user->enterprise_id);

            if (isset($data['user_id']) && $data['user_id'] != '') {
                $leads = $leads->where('user_id', $data['user_id']);
            }

            if (isset($data['year']) && $data['year'] != '') {
                $leads = $leads->whereYear('created_at', $data['year']);
            }

            if (isset($data['month']) && $data['month'] != '') {
                $leads = $leads->whereMonth('created_at', $data['month']);
            }

            if (isset($data['source']) && $data['source'] != '') {
                $string = "%" . $data['source'] . "%";
                $leads = $leads->where('source', 'LIKE', $string);
            }

            if ($user->type == 'atendente') {
                $leads = count($leads->where('user_id', $user->id)->where('status', '0')->get());
            } else if ($user->type == 'administrador') {
                $leads = count($leads->where('status', '0')->get());
            }

            return response()->json($leads, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    // retorna todos so leads
    public function leadsClose(Request $request, Lead $lead)
    {
        try {
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $user = auth('api')->user();
            $data = $request;
            $leads = $lead->where('enterprise_id', $user->enterprise_id);

            // teste
            if ($user->type == 'atendente') {
                $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                    ->where('follow_ups.type', 'n_vendido')
                    ->where('leads.status', '3')
                    ->where('leads.user_id', $user->id);
            } else if ($user->type == 'administrador') {
                $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                    ->where('follow_ups.type', 'n_vendido')
                    ->where('leads.status', '3');
            }

            if (isset($data['user_id']) && $data['user_id'] != '') {
                $leads = $leads->where('leads.user_id', $data['user_id']);
            }

            if (isset($data['year']) && $data['year'] != '') {
                $leads = $leads->whereYear('leads.created_at', $data['year']);
            }

            if (isset($data['month']) && $data['month'] != '') {
                $leads = $leads->whereMonth('leads.created_at', $data['month']);
            }

            if (isset($data['source']) && $data['source'] != '') {
                $string = "%" . $data['source'] . "%";
                $leads = $leads->where('leads.source', 'LIKE', $string);
            }

            return response()->json(count($leads->get()), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    // retorna todos so leads
    public function leadsSales(Request $request, Lead $lead)
    {
        try {
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $user = auth('api')->user();
            $data = $request;
            $leads = $lead->where('enterprise_id', $user->enterprise_id);

            if ($user->type == 'atendente') {
                $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                    ->where('leads.user_id', $user->id)
                    ->where('follow_ups.type', 'vendido')
                    ->where('leads.status', '2');
            } else if ($user->type == 'administrador') {
                $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                    ->where('follow_ups.type', 'vendido')
                    ->where('leads.status', '2');
            }

            if (isset($data['user_id']) && $data['user_id'] != '') {
                $leads = $leads->where('leads.user_id', $data['user_id']);
            }

            if (isset($data['year']) && $data['year'] != '') {
                $leads = $leads->whereYear('leads.created_at', $data['year']);
            }

            if (isset($data['month']) && $data['month'] != '') {
                $leads = $leads->whereMonth('leads.created_at', $data['month']);
            }

            if (isset($data['source']) && $data['source'] != '') {
                $string = "%" . $data['source'] . "%";
                $leads = $leads->where('leads.source', 'LIKE', $string);
            }

            return response()->json(count($leads->get()), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    // retorna os ranking com o desempenho dos atendentes
    public function ranking()
    {
        try {
            $enterprise = auth('api')->user()->enterprise_id;
            $rank = array();
            $users = User::where('enterprise_id', $enterprise)->where('type', '!=', 'administrador')->get();

            foreach ($users as $user) {
                $leads = Lead::where('user_id', $user->id)->get()->count();

                $sales = count(
                    Lead::join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                        ->where('leads.user_id', $user->id)
                        ->where('follow_ups.type', 'vendido')
                        ->get()
                );

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

    // retorna todos so leads
    public function graphicLead(Request $request, Lead $lead)
    {
        try {
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $data = $request;
            $enterprise = auth('api')->user()->enterprise_id;
            $leads = $lead->where('enterprise_id', $enterprise);

            if (isset($data['user_id']) && $data['user_id'] != '') {
                $leads = $leads->where('leads.user_id', $data['user_id']);
            }

            if (isset($data['year']) && $data['year'] != '') {
                $leads = $leads->whereYear('leads.created_at', $data['year']);
            }

            if (isset($data['month']) && $data['month'] != '') {
                $leads = $leads->whereMonth('leads.created_at', $data['month']);
            }

            if (isset($data['source']) && $data['source'] != '') {
                $string = "%" . $data['source'] . "%";
                $leads = $leads->where('leads.source', 'LIKE', $string);
            }

            $leads = $leads->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();

            $graphic = [];

            foreach ($leads as $value) {
                array_push($graphic, [strtotime($value['date']) * 1000, $value['leads']]);
            }

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
            $data = $request;
            $enterprise = auth('api')->user()->enterprise_id;
            $leads = $lead->where('enterprise_id', $enterprise);

            if (isset($data['user_id']) && $data['user_id'] != '') {
                $leads = $leads->where('leads.user_id', $data['user_id']);
            }

            if (isset($data['year']) && $data['year'] != '') {
                $leads = $leads->whereYear('leads.created_at', $data['year']);
            }

            if (isset($data['month']) && $data['month'] != '') {
                $leads = $leads->whereMonth('leads.created_at', $data['month']);
            }

            if (isset($data['source']) && $data['source'] != '') {
                $string = "%" . $data['source'] . "%";
                $leads = $leads->where('leads.source', 'LIKE', $string);
            }

            $leads = $leads->where('status', '0')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();

            $graphic = [];

            foreach ($leads as $value) {
                array_push($graphic, [strtotime($value['date']) * 1000, $value['leads']]);
            }

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
            $data = $request;
            $enterprise = auth('api')->user()->enterprise_id;
            $leads = $lead->where('enterprise_id', $enterprise);

            if (isset($data['user_id']) && $data['user_id'] != '') {
                $leads = $leads->where('leads.user_id', $data['user_id']);
            }

            if (isset($data['year']) && $data['year'] != '') {
                $leads = $leads->whereYear('leads.created_at', $data['year']);
            }

            if (isset($data['month']) && $data['month'] != '') {
                $leads = $leads->whereMonth('leads.created_at', $data['month']);
            }

            if (isset($data['source']) && $data['source'] != '') {
                $string = "%" . $data['source'] . "%";
                $leads = $leads->where('leads.source', 'LIKE', $string);
            }

            $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('follow_ups.type', 'n_vendido')
                ->where('leads.status', '3')
                ->select(DB::raw('DATE(follow_ups.created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();

            $graphic = [];

            foreach ($leads as $value) {
                array_push($graphic, [strtotime($value['date']) * 1000, $value['leads']]);
            }

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
            $data = $request;
            $enterprise = auth('api')->user()->enterprise_id;
            $leads = $lead->where('enterprise_id', $enterprise);

            if (isset($data['user_id']) && $data['user_id'] != '') {
                $leads = $leads->where('leads.user_id', $data['user_id']);
            }

            if (isset($data['year']) && $data['year'] != '') {
                $leads = $leads->whereYear('leads.created_at', $data['year']);
            }

            if (isset($data['month']) && $data['month'] != '') {
                $leads = $leads->whereMonth('leads.created_at', $data['month']);
            }

            if (isset($data['source']) && $data['source'] != '') {
                $string = "%" . $data['source'] . "%";
                $leads = $leads->where('leads.source', 'LIKE', $string);
            }

            $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('follow_ups.type', 'vendido')
                ->where('leads.status', '2')
                ->select(DB::raw('DATE(follow_ups.created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();

            $graphic = [];

            foreach ($leads as $value) {
                array_push($graphic, [strtotime($value['date']) * 1000, $value['leads']]);
            }

            return response()->json($graphic, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }
}
