<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'activity',
        'module',
        'record_id',
        'old_payload',
        'new_payload',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'old_payload' => 'array',
        'new_payload' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
