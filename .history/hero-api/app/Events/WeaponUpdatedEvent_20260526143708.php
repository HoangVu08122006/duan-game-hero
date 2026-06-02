<?php
namespace App\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class WeaponUpdatedEvent implements ShouldBroadcast {
    public $weapon;
    public function __construct($weapon) {
        $this->weapon = $weapon;
    }
    public function broadcastOn() {
        return new Channel('weapons-channel');
    }
    public function broadcastAs() {
        return 'WeaponUpdatedEvent'; // Tên sự kiện
    }
}
