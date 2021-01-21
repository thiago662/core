<?php

namespace App\Models\Concerns;

trait ConstructChart
{
    public function ConstructChart($data)
    {
        $chart = [];

        foreach ($data as $value) {
            array_push($chart, [strtotime($value['date']) * 1000, $value['leads']]);
        }

        return $chart;
    }
}