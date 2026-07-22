<?php

namespace App\Nova\Resources;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class GamePlayer extends Resource
{
    public static $model = \App\Models\GamePlayer::class;

    public static $title = 'id';

    public static $search = [
        'id',
        'seat',
        'score',
    ];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Game', 'game', Game::class)->sortable(),
            BelongsTo::make('User', 'user', User::class)->sortable(),
            Number::make('Seat')->min(1)->max(4)->step(1)->sortable(),
            Number::make('Score')->min(0)->step(1)->sortable(),
            Code::make('Hand')->json()->hideFromIndex(),
            DateTime::make('Created At')->exceptOnForms()->sortable(),
            DateTime::make('Updated At')->exceptOnForms()->sortable(),
        ];
    }
}
