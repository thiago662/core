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
        $this->middleware('administrator')->only(['leadsTotal', 'leadsOpen', 'leadsFinished']);
    }

    public function leadsTotal()
    {
        try {
            $leads = count(Lead::all());

            return response()->json($leads, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function leadsOpen()
    {
        try {
            $leads = count(Lead::where("status", "0")->get());

            return response()->json($leads, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function leadsFinished()
    {
        try {
            $leads = count(Lead::where("status", "2")->get());

            return response()->json($leads, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function leadsSales()
    {
        try {
            $leads = count(
                Lead::join('follow_ups','leads.id','=','follow_ups.lead_id')
                    ->where('follow_ups.type', 'vendido')
                    ->where('leads.status', 2)
                    ->get()
            );

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
            $users = User::where("type", "!=", "administrador")->get();

            foreach ($users as $user) {
                $leads = Lead::where("user_id", $user->id)->get()->count();

                $sales = count(
                    Lead::join('follow_ups','leads.id','=','follow_ups.lead_id')
                        ->where('leads.user_id', $user->id)
                        ->where('follow_ups.type', 'vendido')
                        ->get()
                );

                array_push($teste, array("id" => $user->id, "user" => $user->name, "leads" => $leads, "sales" => $sales));
            }
            $teste1 = array_column($teste, 'sales');
            array_multisort($teste1, SORT_DESC, $teste);

            return response()->json($teste, 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());

            return response()->json($message->getMessage(), 401);
        }
    }

    public function graphic()
    {
        $teste = Lead::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as leads'))
            ->groupBy('date')
            ->get();

        $teste1 = [];

        foreach($teste as $value){
            array_push($teste1, [strtotime($value['date']) * 1000,$value['leads']]);
        }

        return response()->json($teste1, 200);
    }
}
