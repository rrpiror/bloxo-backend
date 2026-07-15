<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GameCell;
use App\Models\GamePlayer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GameEngine
{
    public const BOARD_SIZE = 40;
    public const HAND_SIZE = 6;
    public const STARTER_X = 20;
    public const STARTER_Y = 20;

    public function createGame(User $host): Game
    {
        return DB::transaction(function () use ($host) {
            $deck = $this->createShuffledDeck();
            $discard = [$this->draw($deck)];
            $starterCode = $this->drawStarterTile($deck);

            $game = Game::create([
                'invite_code' => strtoupper(Str::random(6)),
                'status' => 'waiting',
                'current_player_id' => 0,
                'deck' => $deck,
                'discard_pile' => $discard,
            ]);

            $game->players()->create([
                'user_id' => $host->id,
                'seat' => 1,
                'score' => 0,
                'hand' => $this->drawHand($deck, 'p1'),
            ]);

            $game->cells()->create([
                'board_index' => (self::STARTER_Y * self::BOARD_SIZE) + self::STARTER_X,
                'tile_id' => 'starter',
                'colors' => $starterCode,
                'locked' => true,
            ]);

            $game->deck = $deck;
            $game->save();

            return $game->fresh(['players.user', 'cells']);
        });
    }

    public function joinGame(Game $game, User $guest): Game
    {
        return DB::transaction(function () use ($game, $guest) {
            $game->refresh();
            if ($game->status !== 'waiting') {
                abort(422, 'Game is not joinable.');
            }
            if ($game->players()->count() >= 2) {
                abort(422, 'Game already has two players.');
            }

            $deck = $game->deck ?? [];
            $game->players()->create([
                'user_id' => $guest->id,
                'seat' => 2,
                'score' => 0,
                'hand' => $this->drawHand($deck, 'p2'),
            ]);

            $hostPlayer = $game->players()->orderBy('seat')->firstOrFail();
            $game->update([
                'status' => 'active',
                'current_player_id' => $hostPlayer->user_id,
                'deck' => $deck,
            ]);

            return $game->fresh(['players.user', 'cells']);
        });
    }

    public function snapshot(Game $game, User $viewer): array
    {
        $game->loadMissing(['players.user', 'cells']);
        $viewerPlayer = $game->players->firstWhere('user_id', $viewer->id);
        abort_if(!$viewerPlayer, 403, 'You are not part of this game.');
        $opponent = $game->players->firstWhere('user_id', '!=', $viewer->id);

        return [
            'game_id' => $game->id,
            'invite_code' => $game->invite_code,
            'status' => $game->status,
            'current_player_id' => $game->current_player_id,
            'human_player_id' => $viewer->id,
            'opponent_name' => $opponent?->user?->name ?? 'Waiting...',
            'human_score' => $viewerPlayer->score,
            'opponent_score' => $opponent?->score ?? 0,
            'human_hand' => $viewerPlayer->hand,
            'board' => $game->cells->mapWithKeys(function (GameCell $cell) {
                return [$cell->board_index => [
                    'id' => $cell->tile_id,
                    'colors' => $cell->colors,
                    'locked' => $cell->locked,
                ]];
            }),
            'deck_count' => count($game->deck ?? []),
            'discard_count' => count($game->discard_pile ?? []),
            'winner_text' => $game->winner_text,
        ];
    }

    public function move(Game $game, User $viewer, string $tileId, int $boardIndex, string $colors): Game
    {
        return DB::transaction(function () use ($game, $viewer, $tileId, $boardIndex, $colors) {
            $game = Game::query()->whereKey($game->id)->lockForUpdate()->firstOrFail();
            $game->load(['players.user', 'cells']);

            abort_if($game->status !== 'active', 422, 'Game is not active.');
            abort_if($game->current_player_id !== $viewer->id, 422, 'It is not your turn.');

            $player = $game->players->firstWhere('user_id', $viewer->id);
            $opponent = $game->players->firstWhere('user_id', '!=', $viewer->id);
            abort_if(!$player || !$opponent, 422, 'Game is missing a player.');

            $hand = $player->hand ?? [];
            $handIndex = collect($hand)->search(fn ($tile) => is_array($tile) && ($tile['id'] ?? null) === $tileId);
            abort_if($handIndex === false, 422, 'Tile is not in your hand.');
            abort_if($game->cells->contains('board_index', $boardIndex), 422, 'Cell is occupied.');

            $validation = $this->checkPlacement($game, $boardIndex, $colors);
            abort_if(!$validation['valid'], 422, $validation['message']);

            $game->cells()->create([
                'board_index' => $boardIndex,
                'tile_id' => $tileId,
                'colors' => $colors,
                'locked' => true,
            ]);

            $hand[$handIndex] = null;
            $deck = $game->deck ?? [];
            $this->drawToFirstEmptySlot($hand, $deck, $player->seat === 1 ? 'p1' : 'p2');
            $points = $this->scorePlacement($game->fresh('cells'), $boardIndex, $colors);

            $player->update([
                'hand' => $hand,
                'score' => $player->score + $points,
            ]);

            $game->update([
                'deck' => $deck,
                'current_player_id' => $opponent->user_id,
            ]);

            if (empty($deck) && $this->tileCount($player->fresh()) === 0 && $this->tileCount($opponent->fresh()) === 0) {
                $winnerText = $player->fresh()->score >= $opponent->fresh()->score ? 'YOU WIN' : 'YOU LOSE';
                $game->update([
                    'status' => 'finished',
                    'winner_text' => $winnerText,
                ]);
            }

            return $game->fresh(['players.user', 'cells']);
        });
    }

    public function checkPlacement(Game $game, int $idx, string $colors): array
    {
        $cells = $game->cells->keyBy('board_index');
        $neighbors = [
            ['off' => -40, 'a' => [0, 1], 'n' => [2, 3]],
            ['off' => 40, 'a' => [2, 3], 'n' => [0, 1]],
            ['off' => -1, 'a' => [0, 2], 'n' => [1, 3]],
            ['off' => 1, 'a' => [1, 3], 'n' => [0, 2]],
        ];

        $hasAdjacent = false;
        foreach ($neighbors as $item) {
            $off = $item['off'];
            $nIdx = $idx + $off;
            if ($off === -1 && $idx % self::BOARD_SIZE === 0) continue;
            if ($off === 1 && ($idx + 1) % self::BOARD_SIZE === 0) continue;
            if ($nIdx < 0 || $nIdx >= self::BOARD_SIZE * self::BOARD_SIZE) continue;
            $neighbor = $cells->get($nIdx);
            if (!$neighbor) continue;
            $hasAdjacent = true;
            $nc = $neighbor->colors;
            if ($colors[$item['a'][0]] !== $nc[$item['n'][0]] || $colors[$item['a'][1]] !== $nc[$item['n'][1]]) {
                return ['valid' => false, 'message' => 'Colour mismatch.'];
            }
        }

        if (!$hasAdjacent) {
            return ['valid' => false, 'message' => 'No adjacent tiles.'];
        }

        return ['valid' => true, 'message' => null];
    }

    public function scorePlacement(Game $game, int $idx, string $colors): int
    {
        $cells = $game->cells->keyBy('board_index');
        $checks = [
            ['q' => 0, 'off' => -40, 'nq' => 2],
            ['q' => 0, 'off' => -1, 'nq' => 1],
            ['q' => 1, 'off' => -40, 'nq' => 3],
            ['q' => 1, 'off' => 1, 'nq' => 0],
            ['q' => 2, 'off' => 40, 'nq' => 0],
            ['q' => 2, 'off' => -1, 'nq' => 3],
            ['q' => 3, 'off' => 40, 'nq' => 1],
            ['q' => 3, 'off' => 1, 'nq' => 2],
        ];

        $total = 0;
        $counted = [];

        for ($i = 0; $i < 4; $i++) {
            $connected = false;
            foreach ($checks as $check) {
                if ($check['q'] !== $i) continue;
                $nIdx = $idx + $check['off'];
                $neighbor = $cells->get($nIdx);
                if ($neighbor && $neighbor->colors[$check['nq']] === $colors[$i]) {
                    $connected = true;
                }
            }
            if (!$connected) continue;
            $node = $idx . '-' . $i;
            if (in_array($node, $counted, true)) continue;
            $block = $this->floodFill($cells->toArray(), $idx, $i, $colors[$i], $colors);
            $counted = array_merge($counted, $block);
            $total += count($block);
        }

        return $total;
    }

    private function floodFill(array $cells, int $startIdx, int $startQuad, string $color, string $activeColors): array
    {
        $found = [];
        $queue = [[$startIdx, $startQuad]];
        $internal = [0 => [1, 2], 1 => [0, 3], 2 => [0, 3], 3 => [1, 2]];
        $external = [
            ['q' => 0, 'off' => -40, 'nq' => 2],
            ['q' => 0, 'off' => -1, 'nq' => 1],
            ['q' => 1, 'off' => -40, 'nq' => 3],
            ['q' => 1, 'off' => 1, 'nq' => 0],
            ['q' => 2, 'off' => 40, 'nq' => 0],
            ['q' => 2, 'off' => -1, 'nq' => 3],
            ['q' => 3, 'off' => 40, 'nq' => 1],
            ['q' => 3, 'off' => 1, 'nq' => 2],
        ];

        while ($queue) {
            [$currentIndex, $currentQuad] = array_shift($queue);
            $key = $currentIndex . '-' . $currentQuad;
            if (in_array($key, $found, true)) continue;
            $found[] = $key;

            $currentColors = $currentIndex === $startIdx ? $activeColors : ($cells[$currentIndex]->colors ?? null);
            if (!$currentColors) continue;

            foreach ($internal[$currentQuad] as $nextQuad) {
                if ($currentColors[$nextQuad] === $color) {
                    $queue[] = [$currentIndex, $nextQuad];
                }
            }

            foreach ($external as $edge) {
                if ($edge['q'] !== $currentQuad) continue;
                $nextIndex = $currentIndex + $edge['off'];
                if (!isset($cells[$nextIndex])) continue;
                if ($cells[$nextIndex]->colors[$edge['nq']] === $color) {
                    $queue[] = [$nextIndex, $edge['nq']];
                }
            }
        }

        return $found;
    }

    private function createShuffledDeck(): array
    {
        $deck = [];
        $mono = ['GGGG', 'YYYY', 'BBBB', 'RRRR'];
        $bisect = ['GGRR', 'GGBB', 'GGYY', 'RRBB', 'RRYY', 'BBYY'];
        $quads = ['RYGB', 'RYBG', 'RBGY', 'RBYG', 'RGBY', 'RGYB'];
        foreach ($mono as $item) $deck[] = $item;
        foreach ($bisect as $item) { $deck[] = $item; $deck[] = $item; }
        foreach ($quads as $item) { for ($i = 0; $i < 4; $i++) $deck[] = $item; }
        shuffle($deck);
        return $deck;
    }

    private function draw(array &$deck): string
    {
        return array_pop($deck);
    }

    private function drawStarterTile(array &$deck): string
    {
        $candidates = array_values(array_filter(
            array_keys($deck),
            fn ($index) => ! $this->isMonoTile($deck[$index])
        ));

        if (empty($candidates)) {
            return $this->draw($deck);
        }

        $index = $candidates[array_rand($candidates)];
        $tile = $deck[$index];
        array_splice($deck, $index, 1);

        return $tile;
    }

    private function isMonoTile(string $colors): bool
    {
        return strlen($colors) === 4 && count(array_unique(str_split($colors))) === 1;
    }

    private function drawHand(array &$deck, string $prefix): array
    {
        $hand = array_fill(0, self::HAND_SIZE, null);
        for ($i = 0; $i < self::HAND_SIZE; $i++) {
            $hand[$i] = [
                'id' => $prefix . '_' . Str::lower(Str::random(8)),
                'colors' => $this->draw($deck),
                'locked' => true,
            ];
        }
        return $hand;
    }

    private function drawToFirstEmptySlot(array &$hand, array &$deck, string $prefix): void
    {
        if (empty($deck)) return;
        $index = array_search(null, $hand, true);
        if ($index === false) return;
        $hand[$index] = [
            'id' => $prefix . '_' . Str::lower(Str::random(8)),
            'colors' => $this->draw($deck),
            'locked' => true,
        ];
    }

    private function tileCount(GamePlayer $player): int
    {
        return collect($player->hand ?? [])->filter()->count();
    }
}
