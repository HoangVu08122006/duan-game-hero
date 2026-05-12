<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run() {
    \App\Models\Hero::create([
        'id' => 1, 
        'name' => 'Chiến Binh', 
        'base_attack' => 10,
        'attack_growth_per_lv' => 2 // Thêm giá trị vào đây
    ]);

    \App\Models\Weapon::create([
        'id' => 1, 
        'name' => 'Kiếm Gỗ', 
        'base_attack' => 5, 
        'required_hero_level' => 0,
        'attack_growth_per_upgrade' => 1 // Kiểm tra xem bảng weapons có cần cột này không
    ]);

    \App\Models\Pet::create([
        'id' => 1, 
        'name' => 'Rồng Con', 
        'base_dps' => 5,
        'dps_growth_per_upgrade' => 1 // Kiểm tra tương tự với bảng pets
    ]);
}
}
