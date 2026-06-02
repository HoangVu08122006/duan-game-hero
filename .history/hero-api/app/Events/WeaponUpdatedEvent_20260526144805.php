<?php

namespace App\Events; // DÒNG NÀY LÀ BẮT BUỘC

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets; // Thêm dòng này để event chạy chuẩn
use Illuminate\Foundation\Events\Dispatchable; // Thêm dòng này
use Illuminate\Queue\SerializesModels; // Thêm dòng này
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Weapon;

class WeaponUpdatedEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels; // Thêm các trait này

    public $weapon;

    public function __construct($weapon) {
        $this->weapon = $weapon;
    }

    public function broadcastOn() {
        return new Channel('weapons-channel');
    }

    public function broadcastAs() {
        return 'WeaponUpdatedEvent';
    }
}