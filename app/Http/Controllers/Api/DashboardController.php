<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiMessages;
use App\Models\Lead;
use App\Models\User;
use App\Models\FollowUp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('administrator')->only([]);
    }

    // retorna a quantidade de leads total
    public function leadsTotal(Request $request, Lead $teste)
    {
        try {
            $user = auth('api')->user();
            $data = $request;
            $leads = $teste;

            if (isset($data['user_id']) && $data['user_id'] != '') {
                $leads = $leads->where('user_id', $data['user_id']);
            }

            if (isset($data['year']) && $data['year'] != '') {
                $leads = $leads->whereYear('created_at', $data['year']);
            }

            if (isset($data['mouth']) && $data['mouth'] != '') {
                $leads = $leads->whereMonth('created_at', $data['mouth']);
            }

            if (isset($data['source']) && $data['source'] != '') {
                $teste = "%" . $data['source'] . "%";
                $leads = $leads->where('source', 'LIKE', $teste);
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
    public function leadsOpen(Request $request, Lead $teste)
    {
        try {
            $user = auth('api')->user();
            $data = $request;
            $leads = $teste;

            if (isset($data['user_id']) && $data['user_id'] != '') {
                $leads = $leads->where('user_id', $data['user_id']);
            }

            if (isset($data['year']) && $data['year'] != '') {
                $leads = $leads->whereYear('created_at', $data['year']);
            }

            if (isset($data['mouth']) && $data['mouth'] != '') {
                $leads = $leads->whereMonth('created_at', $data['mouth']);
            }

            if (isset($data['source']) && $data['source'] != '') {
                $teste = "%" . $data['source'] . "%";
                $leads = $leads->where('source', 'LIKE', $teste);
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
    public function leadsClose(Request $request, Lead $teste)
    {
        try {
            $user = auth('api')->user();
            $data = $request;
            $leads = $teste;

            // teste
            if ($user->type == 'atendente') {
                $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                    ->whereIn('follow_ups.type', ['vendido', 'n_vendido'])
                    ->where('leads.status', '2')
                    ->where('leads.user_id', $user->id);
            } else if ($user->type == 'administrador') {
                $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                    ->whereIn('follow_ups.type', ['vendido', 'n_vendido'])
                    ->where('leads.status', '2');
            }

            if (isset($data['user_id']) && $data['user_id'] != '') {
                $leads = $leads->where('leads.user_id', $data['user_id']);
            }

            if (isset($data['year']) && $data['year'] != '') {
                $leads = $leads->whereYear('leads.created_at', $data['year']);
            }

            if (isset($data['mouth']) && $data['mouth'] != '') {
                $leads = $leads->whereMonth('leads.created_at', $data['mouth']);
            }

            if (isset($data['source']) && $data['source'] != '') {
                $teste = "%" . $data['source'] . "%";
                $leads = $leads->where('leads.source', 'LIKE', $teste);
            }

            return response()->json(count($leads->get()), 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    // retorna todos so leads
    public function leadsSales(Request $request, Lead $teste)
    {
        try {
            $user = auth('api')->user();
            $data = $request;
            $leads = $teste;

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

            if (isset($data['mouth']) && $data['mouth'] != '') {
                $leads = $leads->whereMonth('leads.created_at', $data['mouth']);
            }

            if (isset($data['source']) && $data['source'] != '') {
                $teste = "%" . $data['source'] . "%";
                $leads = $leads->where('leads.source', 'LIKE', $teste);
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
            $rank = array();
            $users = User::where('type', '!=', 'administrador')->get();

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
    public function graphicLead(Request $request, Lead $teste)
    {
        try {
            $data = $request;
            $leads = $teste;

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
                $teste = "%" . $data['source'] . "%";
                $leads = $leads->where('leads.source', 'LIKE', $teste);
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
    public function graphicOpen()
    {
        try {
            $data = Lead::where('status', '0')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();

            $graphic = [];

            foreach ($data as $value) {
                array_push($graphic, [strtotime($value['date']) * 1000, $value['leads']]);
            }

            return response()->json($graphic, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    // retorna todos so leads
    public function graphicClose()
    {
        try {
            /* $data = Lead::where('status', '2')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get(); */

            $data = Lead::join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                ->whereIn('follow_ups.type', ['vendido', 'n_vendido'])
                ->where('leads.status', '2')
                ->select(DB::raw('DATE(follow_ups.created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();

            $graphic = [];

            foreach ($data as $value) {
                array_push($graphic, [strtotime($value['date']) * 1000, $value['leads']]);
            }

            return response()->json($graphic, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    // retorna todos so leads
    public function graphicSale()
    {
        try {
            $data = Lead::join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('follow_ups.type', 'vendido')
                ->where('leads.status', '2')
                ->select(DB::raw('DATE(follow_ups.created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();

            $graphic = [];

            foreach ($data as $value) {
                array_push($graphic, [strtotime($value['date']) * 1000, $value['leads']]);
            }

            return response()->json($graphic, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }
}
