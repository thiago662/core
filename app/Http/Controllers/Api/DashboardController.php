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

    public function leadsTotal()
    {
        try {
            $user = auth('api')->user();

            if ($user->type == 'atendente') {
                $leads = count(Lead::where('user_id', $user->id)->get());
            } else if ($user->type == 'administrador') {
                $leads = count(Lead::all());
            }

            return response()->json($leads, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function leadsOpen()
    {
        try {
            $user = auth('api')->user();

            if ($user->type == 'atendente') {
                $leads = count(Lead::where('user_id', $user->id)->where('status', '0')->get());
            } else if ($user->type == 'administrador') {
                $leads = count(Lead::where('status', '0')->get());
            }

            return response()->json($leads, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function leadsFinished()
    {
        try {
            $user = auth('api')->user();

            if ($user->type == 'atendente') {
                $leads = count(Lead::where('user_id', $user->id)->where('status', '2')->get());
            } else if ($user->type == 'administrador') {
                $leads = count(Lead::where('status', '2')->get());
            }

            return response()->json($leads, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function leadsSales()
    {
        try {
            $user = auth('api')->user();

            if ($user->type == 'atendente') {
                $leads = count(
                    Lead::join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                        ->where('leads.user_id', $user->id)
                        ->where('follow_ups.type', 'vendido')
                        ->where('leads.status', 2)
                        ->get()
                );
            } else if ($user->type == 'administrador') {
                $leads = count(
                    Lead::join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                        ->where('follow_ups.type', 'vendido')
                        ->where('leads.status', 2)
                        ->get()
                );
            }

            return response()->json($leads, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function ranking()
    {
        try {
            $teste = array();
            $users = User::where('type', '!=', 'administrador')->get();

            foreach ($users as $user) {
                $leads = Lead::where('user_id', $user->id)->get()->count();

                $sales = count(
                    Lead::join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                        ->where('leads.user_id', $user->id)
                        ->where('follow_ups.type', 'vendido')
                        ->get()
                );

                array_push($teste, array('id' => $user->id, 'user' => $user->name, 'leads' => $leads, 'sales' => $sales));
            }
            $teste1 = array_column($teste, 'sales');
            array_multisort($teste1, SORT_DESC, $teste);

            return response()->json($teste, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function graphicLead()
    {
        try {
            $teste = Lead::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();

            $teste1 = [];

            foreach ($teste as $value) {
                array_push($teste1, [strtotime($value['date']) * 1000, $value['leads']]);
            }

            return response()->json($teste1, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function graphicOpen()
    {
        try {
            $teste = Lead::where('status', '0')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();

            $teste1 = [];

            foreach ($teste as $value) {
                array_push($teste1, [strtotime($value['date']) * 1000, $value['leads']]);
            }

            return response()->json($teste1, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function graphicClose()
    {
        try {
            $teste = Lead::where('status', '2')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();

            $teste1 = [];

            foreach ($teste as $value) {
                array_push($teste1, [strtotime($value['date']) * 1000, $value['leads']]);
            }

            return response()->json($teste1, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function graphicSale()
    {
        try {
            $teste = Lead::join('follow_ups', 'leads.id', '=', 'follow_ups.lead_id')
                ->where('follow_ups.type', 'vendido')
                ->where('leads.status', 2)
                ->select(DB::raw('DATE(follow_ups.created_at) as date'), DB::raw('count(*) as leads'))
                ->groupBy('date')
                ->get();

            $teste1 = [];

            foreach ($teste as $value) {
                array_push($teste1, [strtotime($value['date']) * 1000, $value['leads']]);
            }

            return response()->json($teste1, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }
}
