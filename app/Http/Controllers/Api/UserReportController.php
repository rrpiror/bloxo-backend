<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\ReportedUser;
use App\Models\User;
use Illuminate\Http\Request;

class UserReportController extends Controller
{
    public function store(User $user, Request $request)
    {
        $reporter = $request->user();

        if ($reporter->id === $user->id) {
            return response()->json([
                'message' => 'You cannot report yourself.',
            ], 422);
        }

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:120'],
            'details' => ['nullable', 'string', 'max:1000'],
            'game_id' => ['nullable', 'integer', 'exists:games,id'],
        ]);

        $gameId = $data['game_id'] ?? null;

        if ($gameId !== null) {
            $game = Game::query()
                ->whereKey($gameId)
                ->whereHas('players', fn ($query) => $query->where('user_id', $reporter->id))
                ->whereHas('players', fn ($query) => $query->where('user_id', $user->id))
                ->first();

            if (! $game) {
                return response()->json([
                    'message' => 'You can only report a player from your own game.',
                ], 403);
            }
        }

        ReportedUser::create([
            'reporter_id' => $reporter->id,
            'reported_user_id' => $user->id,
            'game_id' => $gameId,
            'reason' => $data['reason'] ?? 'Inappropriate behaviour',
            'details' => $data['details'] ?? null,
        ]);

        return response()->json([
            'message' => 'Report submitted',
        ], 201);
    }
}
