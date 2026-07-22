<?php

namespace App\Nova\Resources;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class GameCell extends Resource
{
    public static $model = \App\Models\GameCell::class;

    public static $title = 'tile_id';

    public static $search = [
        'id',
        'board_index',
        'tile_id',
        'colors',
    ];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Game', 'game', Game::class)->sortable(),
            Number::make('Board Index')->min(0)->step(1)->sortable(),
            Text::make('Tile ID')->sortable()->rules('required', 'max:255'),
            Text::make('Colours')->rules('required', 'size:4')->sortable(),
            Boolean::make('Locked')->sortable(),
        ];
    }
}
