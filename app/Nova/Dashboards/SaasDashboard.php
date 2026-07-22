<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\AverageRevenuePerAccount;
use App\Nova\Metrics\ChurnRate;
use App\Nova\Metrics\CustomerAcquisitionCost;
use App\Nova\Metrics\CustomerAttritionRate;
use App\Nova\Metrics\MonthlyRecurringRevenue;
use App\Nova\Metrics\OpenReportedUsers;
use Laravel\Nova\Dashboard;

class SaasDashboard extends Dashboard
{
    public function cards(): array
    {
        return [
            new ChurnRate,
            new MonthlyRecurringRevenue,
            new AverageRevenuePerAccount,
            new CustomerAttritionRate,
            new CustomerAcquisitionCost,
            new OpenReportedUsers,
        ];
    }

    public function name(): string
    {
        return 'Bloxo Dashboard';
    }
}
