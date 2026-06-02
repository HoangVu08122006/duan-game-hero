<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonsterSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('monsters')->insert([

            [
                'name' => 'Slime',
                'type' => 'Normal',
                'base_hp' => 100,
                'base_atk' => 10,
                'base_gold' => 50,
                'base_exp' => 20,
                'min_floor' => 1,
                'prefab_name' => 'slime_prefab',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'Goblin',
                'type' => 'Normal',
                'base_hp' => 200,
                'base_atk' => 25,
                'base_gold' => 120,
                'base_exp' => 45,
                'min_floor' => 3,
                'prefab_name' => 'goblin_prefab',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'Skeleton Knight',
                'type' => 'Elite',
                'base_hp' => 500,
                'base_atk' => 70,
                'base_gold' => 350,
                'base_exp' => 120,
                'min_floor' => 8,
                'prefab_name' => 'skeleton_knight_prefab',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'Fire Dragon',
                'type' => 'Boss',
                'base_hp' => 5000,
                'base_atk' => 350,
                'base_gold' => 5000,
                'base_exp' => 1500,
                'min_floor' => 20,
                'prefab_name' => 'fire_dragon_prefab',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}