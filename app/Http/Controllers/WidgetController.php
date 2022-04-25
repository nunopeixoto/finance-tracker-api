<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;

class WidgetController extends Controller
{
    public function show(string $name)
    {
        $data = (new DashboardService)->loadWidget($name);

        return response()->json($data);
    }
}
