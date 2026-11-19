<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringEvent extends Model
{
    protected $table = 'monitoring_events';

    public $timestamps = false;

    protected $fillable = [
        'monitoring_agent_id', 'monitoring_mesin_id', 'event', 'printer', 'doc_name',
        'pages_total', 'pages_done', 'bytes_total', 'bytes_done', 'detail', 'created_at',
    ];

    protected $casts = [
        'detail' => 'array',
        'created_at' => 'datetime',
        'pages_total' => 'integer',
        'pages_done' => 'integer',
        'bytes_total' => 'integer',
        'bytes_done' => 'integer',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(MonitoringMachine::class, 'monitoring_mesin_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(MonitoringAgent::class, 'monitoring_agent_id');
    }
}
