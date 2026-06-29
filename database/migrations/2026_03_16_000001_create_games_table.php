<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('invite_code', 6)->unique();
            $table->string('status')->default('waiting');
            $table->unsignedBigInteger('current_player_id')->default(0);
            $table->json('deck');
            $table->json('discard_pile');
            $table->string('winner_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
