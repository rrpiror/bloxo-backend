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
        $data = $request->validate([
            'max_players' => ['nullable', 'integer', 'min:2', 'max:4'],
        ]);

        $user = $request->user();
        $maxPlayers = (int) ($data['max_players'] ?? 2);

        return DB::transaction(function () use ($user, $maxPlayers) {
            $game = Game::create([
                'invite_code' => $this->generateInviteCode(),
                'status' => 'waiting',
                'current_player_id' => 0,
                'max_players' => $maxPlayers,
                'deck' => [],
                'discard_pile' => [],
                'winner_text' => null,
            ]);

            GamePlayer::create([
                'game_id' => $game->id,
                'user_id' => $user->id,
                'seat' => 1,
                'score' => 0,
                'hand' => [],
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

            $maxPlayers = (int) ($game->max_players ?? 2);

            if ($game->players->count() >= $maxPlayers) {
                return response()->json([
                    'message' => 'Game is full.',
                ], 422);
            }

            $takenSeats = $game->players->pluck('seat')->map(fn ($seat) => (int) $seat)->all();
            $seat = collect(range(1, $maxPlayers))
                ->first(fn ($candidate) => ! in_array($candidate, $takenSeats, true));

            GamePlayer::create([
                'game_id' => $game->id,
                'user_id' => $user->id,
                'seat' => $seat,
                'score' => 0,
                'hand' => [],
            ]);

            $game->load(['players.user', 'cells']);

            return response()->json([
                'game' => $this->serializeGame($game),
            ]);
        });
    }

    public function start(Game $game, Request $request)
    {
        $user = $request->user();

        return DB::transaction(function () use ($game, $user) {
            $game->load(['players.user', 'cells']);

            if ($game->status !== 'waiting') {
                return response()->json([
                    'message' => 'Game has already started.',
                ], 422);
            }

            $host = $game->players->firstWhere('seat', 1);
            if (! $host || (int) $host->user_id !== (int) $user->id) {
                return response()->json([
                    'message' => 'Only the game creator can start the game.',
                ], 403);
            }

            $players = $game->players->sortBy('seat')->values();
            $playerCount = $players->count();

            if ($playerCount < 2) {
                return response()->json([
                    'message' => 'At least two players are needed to start.',
                ], 422);
            }

            if ($playerCount > 4) {
                return response()->json([
                    'message' => 'Bloxo supports up to four players.',
                ], 422);
            }

            $deck = $this->createShuffledDeck();
            $discardPile = [];

            for ($i = 0; $i < $this->discardCountForPlayerCount($playerCount); $i++) {
                if (! empty($deck)) {
                    $discardPile[] = array_pop($deck);
                }
            }

            $game->cells()->delete();

            foreach ($players as $player) {
                $player->update([
                    'score' => 0,
                    'hand' => $this->drawStartingHand($deck, 'p' . $player->seat),
                ]);
            }

            $starterColors = $this->drawStarterTile($deck);

            GameCell::create([
                'game_id' => $game->id,
                'board_index' => 820,
                'tile_id' => 'starter',
                'colors' => $starterColors,
                'locked' => true,
            ]);

            $startingPlayer = $players->random();

            $game->update([
                'status' => 'active',
                'current_player_id' => $startingPlayer->id,
                'deck' => array_values($deck),
                'discard_pile' => array_values($discardPile),
                'winner_text' => null,
            ]);

            $game->refresh()->load(['players.user', 'cells']);

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

    public function destroy(Game $game, Request $request)
    {
        $user = $request->user();

        $player = $game->players()->where('user_id', $user->id)->first();
        if (! $player) {
            return response()->json([
                'message' => 'You are not part of this game.',
            ], 403);
        }

        $game->delete();

        return response()->json([
            'message' => 'Game removed',
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
            'max_players' => (int) ($game->max_players ?? 2),
            'winner_text' => $game->winner_text,
            'deck_count' => count($game->deck ?? []),
            'discard_pile' => $game->discard_pile ?? [],
            'board' => $board,
            'players' => $game->players->sortBy('seat')->map(function ($player) {
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

    protected function drawStarterTile(array &$deck): string
    {
        $candidates = array_values(array_filter(
            array_keys($deck),
            fn ($index) => ! $this->isMonoTile($deck[$index])
        ));

        if (empty($candidates)) {
            return array_pop($deck);
        }

        $index = $candidates[array_rand($candidates)];
        $tile = $deck[$index];
        array_splice($deck, $index, 1);

        return $tile;
    }

    protected function isMonoTile(string $colors): bool
    {
        return strlen($colors) === 4 && count(array_unique(str_split($colors))) === 1;
    }

    protected function discardCountForPlayerCount(int $playerCount): int
    {
        return match ($playerCount) {
            2 => 1,
            3 => 0,
            4 => 3,
            default => 0,
        };
    }

    protected function drawStartingHand(array &$deck, string $prefix): array
    {
        $hand = [];

        for ($i = 0; $i < 6; $i++) {
            $hand[] = [
                'id' => $prefix . '_' . ($i + 1),
                'colors' => array_pop($deck),
                'locked' => false,
            ];
        }

        return $hand;
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

            if ($game->status !== 'active') {
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

            $nextPlayer = $this->nextPlayerAfter($game, $player);
            if (! $nextPlayer) {
                return response()->json([
                    'message' => 'Waiting for more players.',
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
                'p' . $player->seat,
            );

            $player->update([
                'hand' => array_values($hand),
                'score' => (int) $player->score + $score,
            ]);

            $game->update([
                'deck' => array_values($deck),
                'status' => 'active',
                'current_player_id' => $nextPlayer->id,
            ]);

            $game->refresh()->load(['players.user', 'cells']);
            $game->update([
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

            if ($game->status !== 'active') {
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

            $nextPlayer = $this->nextPlayerAfter($game, $player);
            if (! $nextPlayer) {
                return response()->json([
                    'message' => 'Waiting for more players.',
                ], 422);
            }

            $hand = $player->hand ?? [];
            if ($this->handTileCount($player) === 0) {
                return response()->json([
                    'message' => 'No tile available to discard.',
                ], 422);
            }

            $tileIndex = -1;

            if (! empty($data['tile_id'])) {
                $tileIndex = $this->findTileIndexInHand($hand, $data['tile_id']);
            }

            if ($tileIndex === -1) {
                $tileIndex = $this->findFirstTileIndexInHand($hand);
            }

            if ($tileIndex === -1 || empty($hand[$tileIndex])) {
                return response()->json([
                    'message' => 'No tile available to discard.',
                ], 422);
            }

            $discarded = $hand[$tileIndex];
            $hand[$tileIndex] = null;

            $discardPile = $game->discard_pile ?? [];
            $discardPile[] = $discarded['colors'];

            $deck = $game->deck ?? [];
            $this->drawTileIntoFirstEmptySlot(
                $hand,
                $deck,
                'p' . $player->seat,
            );

            $player->update([
                'hand' => array_values($hand),
            ]);

            $game->update([
                'deck' => array_values($deck),
                'discard_pile' => array_values($discardPile),
                'status' => 'active',
                'current_player_id' => $nextPlayer->id,
            ]);

            $game->refresh()->load(['players.user', 'cells']);
            $game->update([
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

    protected function findFirstTileIndexInHand(array $hand): int
    {
        foreach ($hand as $index => $tile) {
            if (! empty($tile)) {
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

            if ($matchCount !== 2) {
                return [
                    'valid' => false,
                    'message' => 'COLOUR MISMATCH',
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

        $players = $game->players->sortBy('seat')->values();

        if ($players->count() < 2) {
            return null;
        }

        $deckEmpty = empty($game->deck ?? []);

        if (! $deckEmpty) {
            return null;
        }

        $allHandsEmpty = $players->every(fn ($player) => $this->handTileCount($player) === 0);

        $anyValidMove = $players->contains(function ($player) use ($game) {
            return $this->handTileCount($player) > 0 && $this->hasAnyValidMove($game, $player);
        });

        if (! $allHandsEmpty && $anyValidMove) {
            return null;
        }

        $highestScore = $players->max(fn ($player) => (int) $player->score);
        $winners = $players
            ->filter(fn ($player) => (int) $player->score === $highestScore)
            ->values();

        if ($winners->count() > 1) {
            return 'Draw';
        }

        $winner = $winners->first();

        return ($winner->user?->name ?? 'Player ' . $winner->seat) . ' wins';
    }

    protected function nextPlayerAfter(Game $game, GamePlayer $player): ?GamePlayer
    {
        $players = $game->players->sortBy('seat')->values();

        if ($players->count() < 2) {
            return null;
        }

        $currentIndex = $players->search(fn ($candidate) => (int) $candidate->id === (int) $player->id);

        if ($currentIndex === false) {
            return $players->first();
        }

        return $players[($currentIndex + 1) % $players->count()];
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

    protected function handTileCount(GamePlayer $player): int
    {
        return collect($player->hand ?? [])->filter()->count();
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
            $opponents = $game->players
                ->where('user_id', '!=', $user->id)
                ->sortBy('seat')
                ->values();
            $opponent = $opponents->first();

            return [
                'id' => $game->id,
                'invite_code' => $game->invite_code,
                'status' => $game->status,
                'max_players' => (int) ($game->max_players ?? 2),
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
                'players' => $game->players->sortBy('seat')->map(fn ($player) => [
                    'id' => $player->id,
                    'user_id' => $player->user_id,
                    'seat' => $player->seat,
                    'name' => $player->user?->name ?? 'Player',
                    'score' => $player->score,
                ])->values(),
            ];
        })->values();

        return response()->json([
            'games' => $result,
        ]);
    }
}
