// App\Models\RewardConfig.php
public static function getActiveConfig() {
    return self::where('status', 'active')->first();
}