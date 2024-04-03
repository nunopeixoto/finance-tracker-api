<?php

use App\Services\DashboardService;

return [
    'available_widgets' => [
        DashboardService::WIDGET_LAST_12_MONTHS_MONTLY_BALANCE,
        DashboardService::WIDGET_TOP5_EXPENSE_CATEGORIES_ALL_TIME,
        DashboardService::WIDGET_EXPENSE_EVOLUTION_PER_CATEGORY,
    ]
];
