<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'invite_code',
        'status',
        'current_player_id',
        'deck',
        'discard_pile',
        'winner_text',
    ];

    protected function casts(): array
    {
        return [
            'deck' => 'array',
            'discard_pile' => 'array',
        ];
    }

    public function players()
    {
        return $this->hasMany(GamePlayer::class);
    }

    public function cells()
    {
        return $this->hasMany(GameCell::class);
    }

    public function getRouteKeyName(): string
    {
        return 'invite_code';
    }
}