<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    protected $table = 'admin_logs';

    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'action',
        'table_name',
        'entity_id',
        'description'
    ];
}