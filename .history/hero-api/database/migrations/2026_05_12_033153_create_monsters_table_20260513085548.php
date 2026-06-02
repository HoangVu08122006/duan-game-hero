<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monsters', function (Blueprint $table) {
            $table->id();
            $table->string('name');         
            $table->string('type');

            $table->integer('base_hp');
            $table->integer('base_atk');

            $table->integer('base_gold');

            $table->integer('base_exp');
            // Logic xuất hiện
            $table->integer('min_floor')->default(1); // Tầng thấp nhất quái này bắt đầu xuất hiện
            $table->string('prefab_name');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monsters');
    }
};