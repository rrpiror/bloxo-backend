<?php

namespace App\Nova\Metrics;

use App\Services\Admin\SaasMetricsService;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class CustomerAcquisitionCost extends Value
{
    public function calculate(NovaRequest $request)
    {
        $pounds = app(SaasMetricsService::class)->customerAcquisitionCost() / 100;

        return $this->result(number_format($pounds, 2))
            ->prefix('£');
    }

    public function name(): string
    {
        return 'Customer Acquisition Cost';
    }
}
