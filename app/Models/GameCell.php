<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameCell extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'game_id',
        'board_index',
        'tile_id',
        'colors',
        'locked',
    ];

    protected function casts(): array
    {
        return [
            'locked' => 'boolean',
        ];
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}