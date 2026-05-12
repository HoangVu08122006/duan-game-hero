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
    \App\Models\Hero::create(['id' => 1, 'name' => 'Chiến Binh', 'base_attack' => 10]);
    \App\Models\Weapon::create(['id' => 1, 'name' => 'Kiếm Gỗ', 'base_attack' => 5, 'required_hero_level' => 0]);
    \App\Models\Pet::create(['id' => 1, 'name' => 'Rồng Con', 'base_dps' => 5]);
}
}
