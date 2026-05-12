<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pets', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->integer('base_dps');

            $table->integer('dps_growth_per_upgrade');

            $table->string('skill_1_name')->nullable();
            $table->string('skill_2_name')->nullable();
            $table->string('skill_3_name')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};