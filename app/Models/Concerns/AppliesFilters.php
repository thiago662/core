<?php

namespace App\Models\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait AppliesFilters
{
    // /**
    //  * Esse comentario é para estudos
    //  * 
    //  * Get the JSON decoded body of the response as an array or scalar value.
    //  *
    //  * @param  Reqiest|null  $request
    //  * @param  mixed  $query
    //  * @return lead
    //  */
    public function filterDashboard(Request $request, $query)
    {
        if ($request->has('source') && $request->get('source')) {
            $query = $query->where('leads.source', 'LIKE', '%' . Str::lower($request['source']) . '%');
        }

        if ($request->has('status') && $request->get('status') != "") {
            $query = $query->where('leads.status', $request['status']);
        }

        if ($request->has('user_id') && $request->get('user_id')) {
            $query = $query->where('leads.user_id', $request['user_id']);
        }

        if ($request->has('year') && $request->get('year')) {
            $query = $query->whereYear('leads.created_at', $request['year']);
        } else {
            $query = $query->whereYear('leads.created_at', date('Y'));
        }

        if ($request->has('month') && $request->get('month')) {
            $query = $query->whereMonth('leads.created_at', $request['month']);
        } else if ((!$request->has('month') || !$request->get('month')) && (!$request->has('year') || !$request->get('year'))) {
        } else {
            $query = $query->whereMonth('leads.created_at', date('m'));
        }

        return $query;
    }
}
