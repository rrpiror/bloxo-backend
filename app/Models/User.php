<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function gamePlayers()
    {
        return $this->hasMany(GamePlayer::class);
    }

    public function reportedUsers()
    {
        return $this->hasMany(ReportedUser::class, 'reporter_id');
    }

    public function reportsAgainst()
    {
        return $this->hasMany(ReportedUser::class, 'reported_user_id');
    }

    public function resolvedReports()
    {
        return $this->hasMany(ReportedUser::class, 'resolved_by');
    }
}
