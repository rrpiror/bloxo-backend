<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamePlayer extends Model
{
    protected $fillable = [
        'game_id',
        'user_id',
        'seat',
        'score',
        'hand',
    ];

    protected function casts(): array
    {
        return [
            'hand' => 'array',
        ];
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}