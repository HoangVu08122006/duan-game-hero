<?php
namespace App\Models;

use App\Models\RewardConfig;
use App\Models\RewardItem;

class RewardService {
    public static function getActiveRewards() {
        $config = RewardConfig::getActiveConfig();
        if (!$config) return collect();
        
        return RewardItem::where('config_id', $config->id)
            ->orderBy('day_index', 'asc')
            ->get();
    }
}