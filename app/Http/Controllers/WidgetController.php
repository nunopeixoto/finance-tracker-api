<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    public function show(Request $request, string $name)
    {
        $data = (new DashboardService)->loadWidget($name, $request->query());

        return response()->json($data);
    }
}
