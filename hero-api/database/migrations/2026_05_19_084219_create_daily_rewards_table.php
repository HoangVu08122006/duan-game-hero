<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            
            // Lưu ngày nhận thưởng gần nhất dưới dạng chuỗi Y-m-d (Ví dụ: 2026-05-19) theo mốc 5h sáng
            $table->date('last_claimed_date')->nullable();
            
            // Ngày hiện tại trong chu kỳ tuần (Giá trị từ 1 đến 7)
            $table->integer('current_streak_day')->default(1); 

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_rewards');
    }
};