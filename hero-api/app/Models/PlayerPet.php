<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerPet extends Model
{
    protected $fillable = ['player_id', 'pet_id', 'level'];
}