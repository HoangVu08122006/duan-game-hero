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

        // Dữ liệu mẫu nạp vào DB: Chỉ số gốc của tất cả Boss đều NHƯ NHAU
$bosses = [
    [
        'name' => 'Ma Vương Tế Bào',
        'base_hp' => 500,
        'base_attack' => 50,
        'base_gold' => 100,
        'base_exp' => 150,
        // Dùng json_encode để biến mảng thành chuỗi JSON hợp lệ
        'skills' => json_encode(['Gầm thét']), 
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'name' => 'Khổng Lồ Đá',
        'base_hp' => 500,
        'base_attack' => 50,
        'base_gold' => 100,
        'base_exp' => 150,
        'skills' => json_encode(['Đập đất']),
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'name' => 'Ác Quỷ Cánh Cụt',
        'base_hp' => 500,
        'base_attack' => 50,
        'base_gold' => 100,
        'base_exp' => 150,
        'skills' => json_encode(['Băng giá']),
        'created_at' => now(),
        'updated_at' => now(),
    ]
];
        // Chèn mảng dữ liệu vào database
        Boss::insert($bosses);
    }
}