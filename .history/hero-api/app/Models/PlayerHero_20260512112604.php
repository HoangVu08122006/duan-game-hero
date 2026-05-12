<?php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerHero extends Model
{
    // Quan trọng nhất là dòng này để hết lỗi MassAssignment
    protected $fillable = ['player_id', 'hero_id', 'is_active'];
}