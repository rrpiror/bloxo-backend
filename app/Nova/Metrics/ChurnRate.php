<?php

namespace App\Nova\Metrics;

use App\Services\Admin\SaasMetricsService;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class ChurnRate extends Value
{
    public function calculate(NovaRequest $request)
    {
        return $this->result(round(app(SaasMetricsService::class)->churnRate(), 2))
            ->suffix('%');
    }

    public function name(): string
    {
        return 'Churn Rate';
    }
}
