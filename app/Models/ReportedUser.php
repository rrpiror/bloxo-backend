<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportedUser extends Model
{
    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'game_id',
        'reason',
        'details',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
