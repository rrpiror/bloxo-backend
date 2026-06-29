<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameCell;
use App\Models\GamePlayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GameController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        return DB::transaction(function () use ($user) {
            $deck = $this->createShuffledDeck();

            $discardPile = [array_pop($deck)];
            $starterColors = array_pop($deck);

            $game = Game::create([
                'invite_code' => $this->generateInviteCode(),
                'status' => 'waiting',
                'current_player_id' => 0,
                'deck' => $deck,
                'discard_pile' => $discardPile,
                'winner_text' => null,
            ]);

            $hand = [];
            for ($i = 0; $i < 6; $i++) {
                $hand[] = [
                    'id' => 'p1_' . ($i + 1),
                    'colors' => array_pop($deck),
                    'locked' => false,
                ];
            }

            $game->update([
                'deck' => $deck,
            ]);

            $playerOne = GamePlayer::create([
                'game_id' => $game->id,
                'user_id' => $user->id,
                'seat' => 1,
                'score' => 0,
                'hand' => $hand,
            ]);

            $game->update([
                'current_player_id' => $playerOne->id,
            ]);

            GameCell::create([
                'game_id' => $game->id,
                'board_index' => 820,
                'tile_id' => 'starter',
                'colors' => $starterColors,
                'locked' => true,
            ]);

            $game->load(['players.user', 'cells']);

            return response()->json([
                'game' => $this->serializeGame($game),
            ], 201);
        });
    }

    public function join(Request $request)
    {
        $request->validate([
            'invite_code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($request, $user) {
            $game = Game::where('invite_code', strtoupper($request->invite_code))
                ->with(['players.user', 'cells'])
                ->firstOrFail();

            if ($game->status !== 'waiting') {
                return response()->json([
                    'message' => 'Game is not available to join.',
                ], 422);
            }

            $alreadyJoined = $game->players->firstWhere('user_id', $user->id);
            if ($alreadyJoined) {
                return response()->json([
                    'game' => $this->serializeGame($game),
                ]);
            }

            if ($game->players->count() >= 2) {
                return response()->json([
                    'message' => 'Game already has two players.',
                ], 422);
            }

            $deck = $game->deck ?? [];
            $hand = [];

            for ($i = 0; $i < 6; $i++) {
                $hand[] = [
                    'id' => 'p2_' . ($i + 1),
                    'colors' => array_pop($deck),
                    'locked' => false,
                ];
            }

            GamePlayer::create([
                'game_id' => $game->id,
                'user_id' => $user->id,
                'seat' => 2,
                'score' => 0,
                'hand' => $hand,
            ]);

            $game->update([
                'status' => 'active',
                'deck' => $deck,
            ]);

            $game->load(['players.user', 'cells']);

            return response()->json([
                'game' => $this->serializeGame($game),
            ]);
        });
    }

    public function show(Game $game, Request $request)
    {
        $user = $request->user();

        $player = $game->players()->where('user_id', $user->id)->first();
        if (! $player) {
            return response()->json([
                'message' => 'You are not part of this game.',
            ], 403);
        }

        $game->load(['players.user', 'cells']);

        return response()->json([
            'game' => $this->serializeGame($game),
        ]);
    }

    protected function serializeGame(Game $game): array
    {
        $totalCells = 40 * 40;
        $board = array_fill(0, $totalCells, null);

        foreach ($game->cells as $cell) {
            $board[$cell->board_index] = [
                'id' => $cell->tile_id,
                'colors' => $cell->colors,
                'locked' => (bool) $cell->locked,
            ];
        }

        return [
            'id' => $game->id,
            'invite_code' => $game->invite_code,
            'status' => $game->status,
            'current_player_id' => $game->current_player_id,
            'winner_text' => $game->winner_text,
            'deck_count' => count($game->deck ?? []),
            'discard_pile' => $game->discard_pile ?? [],
            'board' => $board,
            'players' => $game->players->map(function ($player) {
                return [
                    'id' => $player->id,
                    'user_id' => $player->user_id,
                    'seat' => $player->seat,
                    'name' => $player->user?->name ?? 'Player',
                    'score' => $player->score,
                    'hand' => $player->hand ?? [],
                ];
            })->values(),
        ];
    }

    protected function generateInviteCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (Game::where('invite_code', $code)->exists());

        return $code;
    }

    protected function createShuffledDeck(): array
    {
        $mono = ['GGGG', 'YYYY', 'BBBB', 'RRRR'];
        $bisect = ['GGRR', 'GGBB', 'GGYY', 'RRBB', 'RRYY', 'BBYY'];
        $quads = ['RYGB', 'RYBG', 'RBGY', 'RBYG', 'RGBY', 'RGYB'];

        $deck = [];

        foreach ($mono as $tile) {
            $deck[] = $tile;
        }

        foreach ($bisect as $tile) {
            $deck[] = $tile;
            $deck[] = $tile;
        }

        foreach ($quads as $tile) {
            for ($i = 0; $i < 4; $i++) {
                $deck[] = $tile;
            }
        }

        shuffle($deck);

        return $deck;
    }

    public function move(Game $game, Request $request)
    {
        $data = $request->validate([
            'tile_id' => ['required', 'string'],
            'colors' => ['required', 'string', 'size:4'],
            'board_index' => ['required', 'integer', 'min:0', 'max:1599'],
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($game, $user, $data) {
            $game->load(['players.user', 'cells']);

            if ($game->status !== 'active' && $game->status !== 'waiting') {
                return response()->json([
                    'message' => 'Game is not active.',
                ], 422);
            }

            $player = $game->players->firstWhere('user_id', $user->id);
            if (! $player) {
                return response()->json([
                    'message' => 'You are not part of this game.',
                ], 403);
            }

            if ((int) $game->current_player_id !== (int) $player->id) {
                return response()->json([
                    'message' => 'It is not your turn.',
                ], 422);
            }

            $opponent = $game->players->firstWhere('id', '!=', $player->id);
            if (! $opponent) {
                return response()->json([
                    'message' => 'Waiting for opponent.',
                ], 422);
            }

            $hand = $player->hand ?? [];
            $tileIndex = $this->findTileIndexInHand($hand, $data['tile_id']);

            if ($tileIndex === -1) {
                return response()->json([
                    'message' => 'Tile not found in hand.',
                ], 422);
            }

            $handTile = $hand[$tileIndex];
            $originalColors = $handTile['colors'] ?? '';

            if (! $this->isValidRotation($originalColors, $data['colors'])) {
                return response()->json([
                    'message' => 'Invalid tile rotation.',
                ], 422);
            }

            $boardIndex = (int) $data['board_index'];

            if ($game->cells->firstWhere('board_index', $boardIndex)) {
                return response()->json([
                    'message' => 'Cell occupied.',
                ], 422);
            }

            $placement = $this->checkPlacement(
                $game->cells->all(),
                $boardIndex,
                $data['colors'],
            );

            if (! $placement['valid']) {
                return response()->json([
                    'message' => $placement['message'],
                ], 422);
            }

            $score = $this->calculateScore(
                $game->cells->all(),
                $boardIndex,
                $data['colors'],
            );

            GameCell::create([
                'game_id' => $game->id,
                'board_index' => $boardIndex,
                'tile_id' => $data['tile_id'],
                'colors' => $data['colors'],
                'locked' => true,
            ]);

            $hand[$tileIndex] = null;

            $deck = $game->deck ?? [];
            $this->drawTileIntoFirstEmptySlot(
                $hand,
                $deck,
                $player->seat === 1 ? 'p1' : 'p2',
            );

            $player->update([
                'hand' => array_values($hand),
                'score' => (int) $player->score + $score,
            ]);

            $game->update([
                'deck' => array_values($deck),
                'status' => 'active',
                'current_player_id' => $opponent->id,
                'winner_text' => $this->resolveWinnerText($game),
            ]);

            $game->refresh()->load(['players.user', 'cells']);

            return response()->json([
                'game' => $this->serializeGame($game),
                'score_delta' => $score,
            ]);
        });
    }

    public function pass(Game $game, Request $request)
    {
        $data = $request->validate([
            'tile_id' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($game, $user, $data) {
            $game->load(['players.user', 'cells']);

            $player = $game->players->firstWhere('user_id', $user->id);
            if (! $player) {
                return response()->json([
                    'message' => 'You are not part of this game.',
                ], 403);
            }

            if ((int) $game->current_player_id !== (int) $player->id) {
                return response()->json([
                    'message' => 'It is not your turn.',
                ], 422);
            }

            $opponent = $game->players->firstWhere('id', '!=', $player->id);
            if (! $opponent) {
                return response()->json([
                    'message' => 'Waiting for opponent.',
                ], 422);
            }

            $hand = $player->hand ?? [];
            if (empty($hand)) {
                return response()->json([
                    'message' => 'No tile available to discard.',
                ], 422);
            }

            $tileIndex = -1;

            if (! empty($data['tile_id'])) {
                $tileIndex = $this->findTileIndexInHand($hand, $data['tile_id']);
            }

            if ($tileIndex === -1) {
                $tileIndex = 0;
            }

            $discarded = $hand[$tileIndex];
            $hand[$tileIndex] = null;

            $discardPile = $game->discard_pile ?? [];
            $discardPile[] = $discarded['colors'];

            $deck = $game->deck ?? [];
            $this->drawTileIntoFirstEmptySlot(
                $hand,
                $deck,
                $player->seat === 1 ? 'p1' : 'p2',
            );

            $player->update([
                'hand' => array_values($hand),
            ]);

            $game->update([
                'deck' => array_values($deck),
                'discard_pile' => array_values($discardPile),
                'status' => 'active',
                'current_player_id' => $opponent->id,
                'winner_text' => $this->resolveWinnerText($game),
            ]);

            $game->refresh()->load(['players.user', 'cells']);

            return response()->json([
                'game' => $this->serializeGame($game),
            ]);
        });
    }

    protected function findTileIndexInHand(array $hand, string $tileId): int
    {
        foreach ($hand as $index => $tile) {
            if (($tile['id'] ?? null) === $tileId) {
                return $index;
            }
        }

        return -1;
    }

    protected function drawTileIntoFirstEmptySlot(array &$hand, array &$deck, string $prefix): void
    {
        if (empty($deck)) {
            return;
        }

        $emptyIndex = array_search(null, $hand, true);

        if ($emptyIndex === false) {
            return;
        }

        $hand[$emptyIndex] = [
            'id' => $prefix . '_' . Str::lower(Str::random(8)),
            'colors' => array_pop($deck),
            'locked' => false,
        ];
    }

    protected function drawTileIntoHand(array &$hand, array &$deck, string $prefix): void
    {
        if (empty($deck)) {
            return;
        }

        $hand[] = [
            'id' => $prefix . '_' . now()->timestamp . '_' . Str::random(4),
            'colors' => array_pop($deck),
            'locked' => false,
        ];
    }

    protected function isValidRotation(string $original, string $candidate): bool
    {
        $rotated = $original;

        for ($i = 0; $i < 4; $i++) {
            if ($rotated === $candidate) {
                return true;
            }
            $rotated = $this->rotateColors($rotated);
        }

        return false;
    }

    protected function rotateColors(string $colors): string
    {
        $chars = str_split($colors);

        if (count($chars) !== 4) {
            return $colors;
        }

        return $chars[2] . $chars[0] . $chars[3] . $chars[1];
    }

    protected function checkPlacement(array $cells, int $boardIndex, string $colors): array
    {
        $occupied = [];
        foreach ($cells as $cell) {
            $occupied[(int) $cell->board_index] = $cell->colors;
        }

        if (isset($occupied[$boardIndex])) {
            return [
                'valid' => false,
                'message' => 'Cell occupied.',
            ];
        }

        $neighbours = $this->neighbourIndexes($boardIndex);
        $adjacentFound = false;

        foreach ($neighbours as $side => $neighbourIndex) {
            if ($neighbourIndex === null || ! isset($occupied[$neighbourIndex])) {
                continue;
            }

            $adjacentFound = true;

            $matchCount = $this->edgeMatchCount($colors, $occupied[$neighbourIndex], $side);

            if ($matchCount === 0) {
                return [
                    'valid' => false,
                    'message' => 'COLOR MISMATCH',
                ];
            }
        }

        if (! $adjacentFound) {
            return [
                'valid' => false,
                'message' => 'NO ADJACENT TILES',
            ];
        }

        return [
            'valid' => true,
            'message' => null,
        ];
    }

    protected function calculateScore(array $cells, int $boardIndex, string $colors): int
    {
        $total = 0;
        $countedSets = [];

        for ($quad = 0; $quad < 4; $quad++) {
            $connected = false;

            foreach ($this->externalChecks() as $check) {
                if ($check['q'] !== $quad) {
                    continue;
                }

                $neighbourIndex = $boardIndex + $check['off'];
                $neighbour = $this->findCellByBoardIndex($cells, $neighbourIndex);

                if (! $neighbour) {
                    continue;
                }

                $neighbourColors = $neighbour->colors;
                if ($neighbourColors[$check['nq']] === $colors[$quad]) {
                    $connected = true;
                }
            }

            if (! $connected) {
                continue;
            }

            $node = $boardIndex . '-' . $quad;

            $alreadyCounted = false;
            foreach ($countedSets as $set) {
                if (in_array($node, $set, true)) {
                    $alreadyCounted = true;
                    break;
                }
            }

            if ($alreadyCounted) {
                continue;
            }

            $block = $this->floodFill($cells, $boardIndex, $quad, $colors[$quad], $colors);

            $total += count($block);
            $countedSets[] = $block;
        }

        return $total;
    }

    protected function floodFill(array $cells, int $startIndex, int $startQuad, string $color, string $activeColors): array
    {
        $found = [];
        $queue = [
            [$startIndex, $startQuad],
        ];

        $internal = [
            0 => [1, 2],
            1 => [0, 3],
            2 => [0, 3],
            3 => [1, 2],
        ];

        while (! empty($queue)) {
            [$currentIndex, $currentQuad] = array_shift($queue);
            $key = $currentIndex . '-' . $currentQuad;

            if (in_array($key, $found, true)) {
                continue;
            }

            $found[] = $key;

            if ($currentIndex === $startIndex) {
                $currentColors = $activeColors;
            } else {
                $tile = $this->findCellByBoardIndex($cells, $currentIndex);
                $currentColors = $tile?->colors;
            }

            if (! $currentColors) {
                continue;
            }

            foreach ($internal[$currentQuad] as $nextQuad) {
                if (($currentColors[$nextQuad] ?? null) === $color) {
                    $queue[] = [$currentIndex, $nextQuad];
                }
            }

            foreach ($this->externalChecks() as $edge) {
                if ($edge['q'] !== $currentQuad) {
                    continue;
                }

                $nextIndex = $currentIndex + $edge['off'];
                $nextTile = $this->findCellByBoardIndex($cells, $nextIndex);

                if (! $nextTile) {
                    continue;
                }

                if (($nextTile->colors[$edge['nq']] ?? null) === $color) {
                    $queue[] = [$nextIndex, $edge['nq']];
                }
            }
        }

        return $found;
    }

    protected function externalChecks(): array
    {
        return [
            ['q' => 0, 'off' => -40, 'nq' => 2],
            ['q' => 0, 'off' => -1, 'nq' => 1],
            ['q' => 1, 'off' => -40, 'nq' => 3],
            ['q' => 1, 'off' => 1, 'nq' => 0],
            ['q' => 2, 'off' => 40, 'nq' => 0],
            ['q' => 2, 'off' => -1, 'nq' => 3],
            ['q' => 3, 'off' => 40, 'nq' => 1],
            ['q' => 3, 'off' => 1, 'nq' => 2],
        ];
    }

    protected function findCellByBoardIndex(array $cells, int $boardIndex): ?GameCell
    {
        if ($boardIndex < 0 || $boardIndex > 1599) {
            return null;
        }

        foreach ($cells as $cell) {
            if ((int) $cell->board_index === $boardIndex) {
                return $cell;
            }
        }

        return null;
    }

    protected function neighbourIndexes(int $boardIndex): array
    {
        $size = 40;
        $row = intdiv($boardIndex, $size);
        $col = $boardIndex % $size;

        return [
            'top' => $row > 0 ? $boardIndex - $size : null,
            'right' => $col < $size - 1 ? $boardIndex + 1 : null,
            'bottom' => $row < $size - 1 ? $boardIndex + $size : null,
            'left' => $col > 0 ? $boardIndex - 1 : null,
        ];
    }

    protected function edgesMatch(string $candidate, string $neighbour, string $side): bool
    {
        return $this->edgeMatchCount($candidate, $neighbour, $side) === 2;
    }

    protected function edgeMatchCount(string $candidate, string $neighbour, string $side): int
    {
        $c = str_split($candidate);
        $n = str_split($neighbour);

        if (count($c) !== 4 || count($n) !== 4) {
            return 0;
        }

        return match ($side) {
            'top' => (int) ($c[0] === $n[2]) + (int) ($c[1] === $n[3]),
            'right' => (int) ($c[1] === $n[0]) + (int) ($c[3] === $n[2]),
            'bottom' => (int) ($c[2] === $n[0]) + (int) ($c[3] === $n[1]),
            'left' => (int) ($c[0] === $n[1]) + (int) ($c[2] === $n[3]),
            default => 0,
        };
    }

    protected function resolveWinnerText(Game $game): ?string
    {
        $game->loadMissing(['players.user', 'cells']);

        $p1 = $game->players->firstWhere('seat', 1);
        $p2 = $game->players->firstWhere('seat', 2);

        if (! $p1 || ! $p2) {
            return null;
        }

        $deckEmpty = empty($game->deck ?? []);
        $p1HandEmpty = empty($p1->hand ?? []);
        $p2HandEmpty = empty($p2->hand ?? []);

        if (! $deckEmpty) {
            return null;
        }

        $bothHandsEmpty = $p1HandEmpty && $p2HandEmpty;

        $p1HasMove = ! $p1HandEmpty && $this->hasAnyValidMove($game, $p1);
        $p2HasMove = ! $p2HandEmpty && $this->hasAnyValidMove($game, $p2);

        if (! $bothHandsEmpty && ($p1HasMove || $p2HasMove)) {
            return null;
        }

        if ((int) $p1->score > (int) $p2->score) {
            return ($p1->user?->name ?? 'Player 1') . ' wins';
        }

        if ((int) $p2->score > (int) $p1->score) {
            return ($p2->user?->name ?? 'Player 2') . ' wins';
        }

        return 'Draw';
    }

    protected function hasAnyValidMove(Game $game, GamePlayer $player): bool
    {
        $hand = $player->hand ?? [];
        if (empty($hand)) {
            return false;
        }

        $occupied = [];
        foreach ($game->cells as $cell) {
            $occupied[(int) $cell->board_index] = $cell->colors;
        }

        for ($boardIndex = 0; $boardIndex < 1600; $boardIndex++) {
            if (isset($occupied[$boardIndex])) {
                continue;
            }

            foreach ($hand as $tile) {
                $colors = $tile['colors'] ?? null;
                if (! $colors || strlen($colors) !== 4) {
                    continue;
                }

                $rotations = [$colors];
                $r1 = $this->rotateColors($colors);
                $r2 = $this->rotateColors($r1);
                $r3 = $this->rotateColors($r2);

                foreach ([$r1, $r2, $r3] as $rot) {
                    if (! in_array($rot, $rotations, true)) {
                        $rotations[] = $rot;
                    }
                }

                foreach ($rotations as $candidate) {
                    $placement = $this->checkPlacement($game->cells->all(), $boardIndex, $candidate);
                    if ($placement['valid']) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function activeGames(Request $request)
    {
        $user = $request->user();

        $games = Game::query()
            ->whereHas('players', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereNull('winner_text')
            ->with(['players.user'])
            ->latest('updated_at')
            ->get();

        $result = $games->map(function (Game $game) use ($user) {
            $me = $game->players->firstWhere('user_id', $user->id);
            $opponent = $game->players->firstWhere('user_id', '!=', $user->id);

            return [
                'id' => $game->id,
                'invite_code' => $game->invite_code,
                'status' => $game->status,
                'winner_text' => $game->winner_text,
                'updated_at' => optional($game->updated_at)?->toISOString(),
                'is_your_turn' => $me ? (int) $game->current_player_id === (int) $me->id : false,
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
            'games' => $result,
        ]);
    }
}