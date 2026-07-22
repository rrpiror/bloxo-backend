<?php

namespace App\Services\Admin;

use App\Models\SaasMetricInput;

class SaasMetricsService
{
    public function latestInput(): ?SaasMetricInput
    {
        return SaasMetricInput::query()
            ->latest('period_month')
            ->first();
    }

    public function churnRate(?SaasMetricInput $input = null): float
    {
        $input ??= $this->latestInput();

        if (! $input || $input->starting_customers <= 0) {
            return 0.0;
        }

        return ($input->lost_customers / $input->starting_customers) * 100;
    }

    public function monthlyRecurringRevenue(?SaasMetricInput $input = null): int
    {
        $input ??= $this->latestInput();

        return (int) ($input?->monthly_recurring_revenue_cents ?? 0);
    }

    public function averageRevenuePerAccount(?SaasMetricInput $input = null): int
    {
        $input ??= $this->latestInput();

        if (! $input || $input->active_accounts <= 0) {
            return 0;
        }

        return (int) round($input->monthly_recurring_revenue_cents / $input->active_accounts);
    }

    public function customerAttritionRate(?SaasMetricInput $input = null): float
    {
        return $this->churnRate($input);
    }

    public function customerAcquisitionCost(?SaasMetricInput $input = null): int
    {
        $input ??= $this->latestInput();

        if (! $input || $input->new_customers <= 0) {
            return 0;
        }

        return (int) round($input->sales_marketing_cost_cents / $input->new_customers);
    }
}
