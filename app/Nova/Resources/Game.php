<?php

namespace App\Nova\Resources;

use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class Game extends Resource
{
    public static $model = \App\Models\Game::class;

    public static $title = 'invite_code';

    public static $search = [
        'id',
        'invite_code',
        'status',
        'winner_text',
    ];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Invite Code')->sortable()->rules('required', 'size:6'),
            Select::make('Status')->options([
                'waiting' => 'Waiting',
                'active' => 'Active',
                'finished' => 'Finished',
                'stopped' => 'Stopped',
            ])->displayUsingLabels()->sortable(),
            Number::make('Current Player ID')->sortable(),
            Number::make('Max Players')->min(2)->max(4)->step(1)->sortable(),
            Text::make('Winner Text')->nullable(),
            Code::make('Deck')->json()->hideFromIndex(),
            Code::make('Discard Pile')->json()->hideFromIndex(),
            DateTime::make('Created At')->exceptOnForms()->sortable(),
            DateTime::make('Updated At')->exceptOnForms()->sortable(),
            HasMany::make('Players', 'players', GamePlayer::class),
            HasMany::make('Cells', 'cells', GameCell::class),
        ];
    }
}
