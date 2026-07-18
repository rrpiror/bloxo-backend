<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\GameController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GameControllerPlacementTest extends TestCase
{
    public function test_it_rejects_an_edge_when_only_one_symbol_matches(): void
    {
        $controller = new GameController;
        $method = (new ReflectionClass($controller))->getMethod('checkPlacement');
        $method->setAccessible(true);

        $cells = [
            (object) [
                'board_index' => 820,
                'colors' => 'RYYG',
            ],
        ];

        $result = $method->invoke($controller, $cells, 821, 'BRGY');

        $this->assertFalse($result['valid']);
        $this->assertSame('COLOUR MISMATCH', $result['message']);
    }

    public function test_it_accepts_an_edge_when_both_symbols_match(): void
    {
        $controller = new GameController;
        $method = (new ReflectionClass($controller))->getMethod('checkPlacement');
        $method->setAccessible(true);

        $cells = [
            (object) [
                'board_index' => 820,
                'colors' => 'RYYG',
            ],
        ];

        $result = $method->invoke($controller, $cells, 821, 'YRGY');

        $this->assertTrue($result['valid']);
    }
}
