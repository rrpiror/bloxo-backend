<?php

namespace App\Nova\Resources;

use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class ReportedUser extends Resource
{
    public static $model = \App\Models\ReportedUser::class;

    public static $title = 'reason';

    public static $search = [
        'id',
        'reason',
        'details',
        'status',
    ];

    public static function label(): string
    {
        $openReports = \App\Models\ReportedUser::query()->where('status', 'open')->count();

        return $openReports > 0 ? "Reported Users ({$openReports})" : 'Reported Users';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Reporter', 'reporter', User::class)->sortable(),
            BelongsTo::make('Reported User', 'reportedUser', User::class)->sortable(),
            BelongsTo::make('Game', 'game', Game::class)->nullable()->sortable(),
            Text::make('Reason')->sortable()->rules('required', 'max:120'),
            Textarea::make('Details')->nullable()->alwaysShow(),
            Badge::make('Status')->map([
                'open' => 'danger',
                'reviewing' => 'warning',
                'resolved' => 'success',
                'dismissed' => 'info',
            ])->sortable(),
            Select::make('Status')->options([
                'open' => 'Open',
                'reviewing' => 'Reviewing',
                'resolved' => 'Resolved',
                'dismissed' => 'Dismissed',
            ])->displayUsingLabels()->onlyOnForms()->rules('required'),
            Textarea::make('Admin Notes')->nullable()->alwaysShow(),
            DateTime::make('Resolved At')->nullable()->sortable(),
            BelongsTo::make('Resolved By', 'resolver', User::class)->nullable()->sortable(),
            DateTime::make('Created At')->exceptOnForms()->sortable(),
            DateTime::make('Updated At')->exceptOnForms()->sortable(),
        ];
    }
}
