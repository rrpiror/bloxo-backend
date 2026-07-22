<?php

namespace App\Nova\Metrics;

use App\Services\Admin\SaasMetricsService;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class AverageRevenuePerAccount extends Value
{
    public function calculate(NovaRequest $request)
    {
        $pounds = app(SaasMetricsService::class)->averageRevenuePerAccount() / 100;

        return $this->result(number_format($pounds, 2))
            ->prefix('£');
    }

    public function name(): string
    {
        return 'Average Revenue Per Account';
    }
}
