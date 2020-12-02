<?php

namespace App\Api;

class functions
{
    // função responsavel pelo filtro
    public function filter($data, $leads, $enterprise)
    {
        $leads = $this->enterprise($leads, $enterprise);
        $user = auth('api')->user();

        if ($user->type == 'atendente') {
            $leads = $leads->where('leads.user_id', $user->id);
        }
        if (isset($data['user_id']) && $data['user_id'] != '') {
            $leads = $leads->where('leads.user_id', $data['user_id']);
        }
        if (isset($data['status']) && $data['status'] != '') {
            $leads = $leads->where('status', $data['status']);
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