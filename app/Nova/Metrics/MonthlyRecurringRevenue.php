<?php

namespace App\Nova\Metrics;

use App\Services\Admin\SaasMetricsService;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class MonthlyRecurringRevenue extends Value
{
    public function calculate(NovaRequest $request)
    {
        $pounds = app(SaasMetricsService::class)->monthlyRecurringRevenue() / 100;

        return $this->result(number_format($pounds, 2))
            ->prefix('£');
    }

    public function name(): string
    {
        return 'Monthly Recurring Revenue';
    }
}
