<?php

namespace App\Nova\Resources;

use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class User extends Resource
{
    public static $model = \App\Models\User::class;

    public static $title = 'name';

    public static $search = [
        'id',
        'name',
        'email',
    ];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Name')->sortable()->rules('required', 'max:255'),
            Email::make('Email')->sortable()->rules('required', 'email', 'max:255'),
            Password::make('Password')->onlyOnForms()->creationRules('required', 'min:8')->updateRules('nullable', 'min:8'),
            DateTime::make('Created At')->exceptOnForms()->sortable(),
            HasMany::make('Game Players', 'gamePlayers', GamePlayer::class),
            HasMany::make('Reports Made', 'reportedUsers', ReportedUser::class),
            HasMany::make('Reports Against', 'reportsAgainst', ReportedUser::class),
        ];
    }
}
