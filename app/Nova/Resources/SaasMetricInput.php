<?php

namespace App\Nova\Resources;

use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class SaasMetricInput extends Resource
{
    public static $model = \App\Models\SaasMetricInput::class;

    public static $title = 'period_month';

    public static $search = [
        'id',
        'period_month',
        'notes',
    ];

    public static function label(): string
    {
        return 'SaaS Metric Inputs';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            Date::make('Period Month')->sortable()->rules('required', 'date'),
            Number::make('Starting Customers')->min(0)->step(1)->sortable()->help('Customers at the start of the month.'),
            Number::make('Lost Customers')->min(0)->step(1)->sortable()->help('Customers lost during the month.'),
            Number::make('Active Accounts')->min(0)->step(1)->sortable()->help('Active paying accounts used for ARPA.'),
            Number::make('New Customers')->min(0)->step(1)->sortable()->help('New paying customers used for CAC.'),
            Number::make('MRR Pence', 'monthly_recurring_revenue_cents')->min(0)->step(1)->sortable()->help('Monthly recurring revenue in pence.'),
            Number::make('Sales & Marketing Cost Pence', 'sales_marketing_cost_cents')->min(0)->step(1)->sortable()->help('Total sales and marketing spend in pence for CAC.'),
            Textarea::make('Notes')->nullable()->alwaysShow(),
            DateTime::make('Created At')->exceptOnForms()->sortable(),
            DateTime::make('Updated At')->exceptOnForms()->sortable(),
        ];
    }
}
