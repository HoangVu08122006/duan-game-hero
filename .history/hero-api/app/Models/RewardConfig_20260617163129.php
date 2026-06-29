<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardConfig extends Model
{
    public static function getActiveConfig() {
    return self::where('status', 'active')->first();
}
}