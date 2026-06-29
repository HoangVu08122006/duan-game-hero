<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $table = 'rewards';
    public $timestamps = false;
    protected $fillable = ['day_index', 'reward_type', 'amount', 'name'];
}