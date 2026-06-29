<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('game_cells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('board_index');
            $table->string('tile_id');
            $table->string('colors', 4);
            $table->boolean('locked')->default(true);
            $table->unique(['game_id', 'board_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_cells');
    }
};
