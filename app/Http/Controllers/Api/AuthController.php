<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($data);

        $token = $user->createToken('flutter')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('flutter')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function profile(Request $request)
    {
        $user = $request->user();

        $games = Game::query()
            ->whereHas('players', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['players.user'])
            ->latest('updated_at')
            ->limit(30)
            ->get();

        $stats = [
            'wins' => 0,
            'losses' => 0,
            'draws' => 0,
        ];

        $history = $games->map(function (Game $game) use ($user, &$stats) {
            $me = $game->players->firstWhere('user_id', $user->id);
            $opponent = $game->players->firstWhere('user_id', '!=', $user->id);
            $finished = $game->winner_text !== null;
            $result = 'active';

            if ($finished && $me && $opponent) {
                if ((int) $me->score > (int) $opponent->score) {
                    $result = 'win';
                    $stats['wins']++;
                } elseif ((int) $me->score < (int) $opponent->score) {
                    $result = 'loss';
                    $stats['losses']++;
                } else {
                    $result = 'draw';
                    $stats['draws']++;
                }
            }

            return [
                'id' => $game->id,
                'invite_code' => $game->invite_code,
                'status' => $game->status,
                'winner_text' => $game->winner_text,
                'result' => $result,
                'updated_at' => optional($game->updated_at)?->toISOString(),
                'you' => [
                    'id' => $me?->id,
                    'name' => $me?->user?->name ?? 'You',
                    'score' => $me?->score ?? 0,
                ],
                'opponent' => [
                    'id' => $opponent?->id,
                    'name' => $opponent?->user?->name ?? 'Waiting...',
                    'score' => $opponent?->score ?? 0,
                ],
            ];
        })->values();

        return response()->json([
            'user' => $user,
            'can_edit_name' => ! $this->hasActiveGames($user),
            'stats' => $stats,
            'games' => $history,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:32'],
        ]);

        if ($data['name'] !== $user->name && $this->hasActiveGames($user)) {
            return response()->json([
                'message' => 'You cannot change your name while you have active games.',
            ], 422);
        }

        $user->update([
            'name' => $data['name'],
        ]);

        return response()->json([
            'user' => $user->fresh(),
            'can_edit_name' => ! $this->hasActiveGames($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out',
        ]);
    }

    public function destroy(Request $request)
    {
        $user = $request->user();

        DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            $user->delete();
        });

        return response()->json([
            'message' => 'Account deleted',
        ]);
    }

    private function hasActiveGames(User $user): bool
    {
        return Game::query()
            ->whereIn('status', ['waiting', 'active'])
            ->whereHas('players', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();
    }
}
