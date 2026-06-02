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
            $table->string('name')->comment('Tên Boss');
            
            // Chỉ số cơ bản (sẽ được nhân với tỉ lệ tầng trong code)
            $table->bigInteger('base_hp')->default(1000);
            $table->bigInteger('base_attack')->default(100);
            
            // Phần thưởng cơ bản
            $table->integer('base_gold')->default(500);
            $table->integer('base_exp')->default(200);

            /**
             * Cột skills lưu dạng JSON để chứa nhiều kỹ năng và cooldown
             * Ví dụ: [{"name": "Cắn xé", "cd": 3, "dmg": 1.5}, {"name": "Hồi máu", "cd": 5, "heal": 0.2}]
             */
            $table->json('skills')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bosses');
    }
};