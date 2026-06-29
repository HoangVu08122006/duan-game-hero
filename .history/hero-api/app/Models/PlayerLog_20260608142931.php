<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerLog extends Model
{
    public function player() {
    return $this->belongsTo(Player::class);
}
}
