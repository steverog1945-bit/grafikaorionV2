<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringAgent extends Model
{
    protected $table = 'monitoring_agents';

    protected $fillable = [
        'monitoring_mesin_id', 'nama', 'token_hash', 'last_seen_at', 'last_ip', 'aktif',
    ];

    protected $hidden = ['token_hash'];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'aktif' => 'boolean',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(MonitoringMachine::class, 'monitoring_mesin_id');
    }

    public function online(int $menitToleransi = 2): bool
    {
        return $this->last_seen_at !== null
            && $this->last_seen_at->gt(now()->subMinutes($menitToleransi));
    }
}
