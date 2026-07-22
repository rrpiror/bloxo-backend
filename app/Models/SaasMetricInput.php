<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaasMetricInput extends Model
{
    protected $fillable = [
        'period_month',
        'starting_customers',
        'lost_customers',
        'active_accounts',
        'new_customers',
        'monthly_recurring_revenue_cents',
        'sales_marketing_cost_cents',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_month' => 'date',
            'starting_customers' => 'integer',
            'lost_customers' => 'integer',
            'active_accounts' => 'integer',
            'new_customers' => 'integer',
            'monthly_recurring_revenue_cents' => 'integer',
            'sales_marketing_cost_cents' => 'integer',
        ];
    }
}
