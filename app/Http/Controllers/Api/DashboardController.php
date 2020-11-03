<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiMessages;
use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\User;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('administrator')->only(['leadsTotal', 'leadsOpen', 'leadsFinished']);
    }

    public function leadsTotal()
    {
        try {
            $leads = count(Lead::all());

            return response()->json([
                'leads_total' => $leads
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function leadsOpen()
    {
        try {
            $leads = count(Lead::where("status", "0")->get());

            return response()->json([
                'leads_open' => $leads
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function leadsFinished()
    {
        try {
            $leads = count(Lead::where("status", "2")->get());

            return response()->json([
                'leads_finished' => $leads
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function ranking()
    {
        try {
            $teste = array();
            $users = User::where("type", "!=", "administrador")->get();

            foreach ($users as $user) {
                $leads = Lead::where("user_id", $user->id)->get()->count();

                $vendas = count(
                    Lead::join('follow_ups','leads.id','=','follow_ups.lead_id')
                        ->where('leads.user_id', $user->id)
                        ->where('follow_ups.type', 'vendido')
                        ->get()
                );

                array_push($teste, array("id" => $user->id, "user" => $user->name, "leads" => $leads, "vendas" => $vendas));
            }
            $teste1 = array_column($teste, 'vendas');
            array_multisort($teste1, SORT_DESC, $teste);

            return response()->json($teste, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }
}
