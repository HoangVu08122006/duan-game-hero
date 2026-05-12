<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
{
    // Tạo Hero mẫu
    \App\Models\Hero::create(['name' => 'Chiến Binh', 'base_attack' => 10, 'attack_growth_per_lv' => 2]);

    // Tạo Vũ khí mẫu
    \App\Models\Weapon::create(['name' => 'Kiếm Gỗ', 'base_attack' => 5, 'attack_growth_per_upgrade' => 2, 'required_hero_level' => 0]);
    \App\Models\Weapon::create(['name' => 'Kiếm Sắt', 'base_attack' => 20, 'attack_growth_per_upgrade' => 5, 'required_hero_level' => 100]);

    // Tạo Pet mẫu
    \App\Models\Pet::create(['name' => 'Rồng Con', 'base_dps' => 10, 'dps_growth_per_upgrade' => 3, 'skill_1_name' => 'Phun Lửa']);

    // Tạo Quái mẫu
    \App\Models\Monster::create(['base_hp' => 50, 'base_gold' => 10, 'base_exp' => 5]);
}
}
