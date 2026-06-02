<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('password');
            $table->bigInteger('gold')->default(0);

            $table->integer('level')->default(1);
            $table->integer('exp')->default(0);

            // --- CHỈ SỐ GỐC CỦA HERO ---
            $table->integer('base_hp')->default(1000);     // Máu khởi điểm
            $table->integer('base_attack')->default(50); // Sát thương khởi điểm
            // ---------------------------

            $table->integer('current_floor')->default(1);
            $table->integer('highest_floor')->default(1);

            // Cấp độ nâng cấp (Upgrades)
            $table->integer('upgraded_attack_lv')->default(0);
            $table->integer('upgraded_hp_lv')->default(0);
            $table->integer('upgraded_crit_rate_lv')->default(0);
            $table->integer('upgraded_crit_rate_lv')->default(0);
            $table->integer('upgraded_speed_lv')->default(0);

            // Tổng lực chiến (Dùng để xếp hạng)
            $table->bigInteger('total_power')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};