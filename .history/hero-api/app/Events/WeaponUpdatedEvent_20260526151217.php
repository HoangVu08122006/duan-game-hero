<?php

namespace App\Events; // DÒNG NÀY LÀ BẮT BUỘC

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets; // Thêm dòng này để event chạy chuẩn
use Illuminate\Foundation\Events\Dispatchable; // Thêm dòng này
use Illuminate\Queue\SerializesModels; // Thêm dòng này
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Weapon;

class WeaponUpdatedEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Weapon $weapon; // Dùng Type Hint để IDE hết báo đỏ

    public function __construct(Weapon $weapon) {
        $this->weapon = $weapon;
    }


    public function broadcastOn() {
        public function broadcastAs() {
    return 'WeaponUpdatedEvent'; // Để nguyên như cũ
}
    }

    public function broadcastAs() {
        return 'WeaponUpdatedEvent';
    }
}