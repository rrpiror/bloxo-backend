<?php

namespace App\Nova\Metrics;

use App\Models\ReportedUser;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class OpenReportedUsers extends Value
{
    public function calculate(NovaRequest $request)
    {
        return $this->result(
            ReportedUser::query()->where('status', 'open')->count()
        );
    }

    public function name(): string
    {
        return 'Open Reported Users';
    }
}
