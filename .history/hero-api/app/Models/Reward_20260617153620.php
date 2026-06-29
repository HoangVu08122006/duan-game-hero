<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $table = 'reward_configs';
    public $timestamps = false;
    protected $fillable = ['day_index', 'type', 'amount', 'name'];
}