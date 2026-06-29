<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\PlayerPet;
use App\Models\PlayerLog;
use Illuminate\Support\Facades\DB;

class AdminPetController extends Controller
{

 DB::transaction(function () use ($petPivot, $newLevel, $oldLevel, $validated) {
        $petPivot->update(['level' => $newLevel]);

        PlayerLog::create([
            'player_id'  => $validated['player_id'],
            'field_name' => "Admin điều chỉnh cấp độ Pet",
            'old_value'  => "Cấp " . $oldLevel,
            'new_value'  => "Cấp " . $newLevel,
            'created_at' => now(),
        ]);
    });

}

