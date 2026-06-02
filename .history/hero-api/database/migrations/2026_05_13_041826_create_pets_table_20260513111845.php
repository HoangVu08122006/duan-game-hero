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
            $table->string('prefab_name'); // Để Client biết load model Pet nào

            // Chỉ số gốc
            $table->integer('base_dps')->default(50); 
            
            // Tỷ lệ tăng trưởng (Lưu 0.1 tương ứng 10%)
            $table->float('growth_rate')->default(0.1); 

            // Tên và thông tin kỹ năng
            $table->string('skill_1_name')->default('Kỹ năng 1');
            $table->string('skill_2_name')->default('Kỹ năng 2');
            $table->string('skill_3_name')->default('Kỹ năng 3');
            
            // Bạn có thể thêm cột mô tả nếu muốn
            $table->string('skill_1_description')->nullable();
            $table->string('skill_2_description')->nullable();
            $table->string('skill_3_description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};