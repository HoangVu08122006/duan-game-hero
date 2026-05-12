<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bosses', function (Blueprint $table) {
            $table->id();

            $table->integer('base_hp');

            $table->integer('floor_appearance');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bosses');
    }
};