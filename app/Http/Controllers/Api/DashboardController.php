<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiMessages;
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

            $leads = $this->filter($data, $lead, $user->enterprise_id);
            $leads = $this->authorization($user, $leads);

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

            $leads = $this->filter($data, $lead, $user->enterprise_id);
            $leads = $leads->where('status', '0');
            $leads = $this->authorization($user, $leads);

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
            $leads = $this->authorization($user, $leads);
            $leads = $this->filter($data, $leads, $user->enterprise_id);

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

            $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('follow_ups.type', 'vendido')
                ->where('leads.status', '2');
            $leads = $this->authorization($user, $leads);
            $leads = $this->filter($data, $leads, $user->enterprise_id);

            return response()->json($leads->get()->count(), 200);
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

    // retorna todos so leads
    public function graphicLead(Request $request, Lead $lead)
    {
        try {
            $request['source'] = mb_strtolower($request['source'], 'UTF-8');
            $data = $request->all();

            $user = auth('api')->user();

            $leads = $this->filter($data, $lead, $user->enterprise_id);
            $leads = $leads->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();
            $graphic = $this->graphic($leads);

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

            $leads = $this->filter($data, $lead, $user->enterprise_id);
            $leads = $leads->where('status', '0')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();
            $graphic = $this->graphic($leads);

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

            $leads = $this->filter($data, $lead, $user->enterprise_id);
            $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('follow_ups.type', 'n_vendido')
                ->where('leads.status', '3')
                ->select(DB::raw('DATE(follow_ups.created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();
            $graphic = $this->graphic($leads);

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

            $leads = $this->filter($data, $lead, $user->enterprise_id);
            $leads = $leads->join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('follow_ups.type', 'vendido')
                ->where('leads.status', '2')
                ->select(DB::raw('DATE(follow_ups.created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();
            $graphic = $this->graphic($leads);

            return response()->json($graphic, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    // função responsavel pelo filtro
    public function filter($data, $leads, $enterprise)
    {
        $leads = $this->enterprise($leads, $enterprise);

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

        return $leads;
    }

    // função responsavel por converter os dados para o grafico
    public function graphic($leads)
    {
        $graphic = [];

        foreach ($leads as $value) {
            array_push($graphic, [strtotime($value['date']) * 1000, $value['leads']]);
        }

        return $graphic;
    }

    // verifica se é um atendente ou adm
    public function authorization($user, $leads)
    {
        if ($user->type == 'atendente') {
            $leads = $leads->where('user_id', $user->id);
        }

        return $leads;
    }

    // seleciona todos os funcionarios da empresa
    public function enterprise($lead, $enterprise)
    {
        $leads = $lead->where('enterprise_id', $enterprise);

        return $leads;
    }
}
