<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_pets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('player_id')
                ->constrained()
                ->onDelete('cascade');
            $table->boolean('is_equipped')->default(false)->after('level');
            $table->foreignId('pet_id')
                ->constrained()
                ->onDelete('cascade');

            $table->integer('level')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_pets');
    }
};