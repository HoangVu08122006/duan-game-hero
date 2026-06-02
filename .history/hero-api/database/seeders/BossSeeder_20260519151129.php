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
        'base_hp' => 500,       // Chỉ số gốc bằng nhau
        'base_attack' => 50,    // Chỉ số gốc bằng nhau
        'base_gold' => 100,
        'base_exp' => 150,
        'skills' => 'Gầm thét',
    ],
    [
        'name' => 'Khổng Lồ Đá',
        'base_hp' => 500,       // Giống hệt con trên
        'base_attack' => 50,
        'base_gold' => 100,
        'base_exp' => 150,
        'skills' => 'Đập đất',
    ],
    [
        'name' => 'Ác Quỷ Cánh Cụt',
        'base_hp' => 500,       // Giống hệt
        'base_attack' => 50,
        'base_gold' => 100,
        'base_exp' => 150,
        'skills' => 'Băng giá',
    ]
];
        // Chèn mảng dữ liệu vào database
        Boss::insert($bosses);
    }
}