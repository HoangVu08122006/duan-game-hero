<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Boss; // Đảm bảo đã import Model Boss đúng đường dẫn

class BossSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Xóa dữ liệu cũ nếu có để tránh trùng lặp khi chạy lại
        Boss::truncate();

        $bosses = [
            [
                'name' => 'Ma Vương Gác Cổng',
                'base_hp' => 800,
                'base_attack' => 80,
                'base_gold' => 150,
                'base_exp' => 200,
                'skills' => 'Chấn động ground',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rồng Lửa Cổ Đại',
                'base_hp' => 1500,
                'base_attack' => 150,
                'base_gold' => 300,
                'base_exp' => 450,
                'skills' => 'Hơi thở của rồng',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Thần Chết Anubis',
                'base_hp' => 2500,
                'base_attack' => 250,
                'base_gold' => 600,
                'base_exp' => 800,
                'skills' => 'Lời nguyền Pharaoh',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Chèn mảng dữ liệu vào database
        Boss::insert($bosses);
    }
}