<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MonitoringMachine extends Model
{
    protected $table = 'monitoring_mesins';

    protected $fillable = [
        'id_mesin', 'nama_mesin', 'tipe_mesin', 'software',
        'pc_ip', 'printer_name', 'aktif', 'catatan',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'id_mesin' => 'integer',
    ];

    public function agents(): HasMany
    {
        return $this->hasMany(MonitoringAgent::class, 'monitoring_mesin_id');
    }

    public function latestAgent(): HasOne
    {
        return $this->hasOne(MonitoringAgent::class, 'monitoring_mesin_id')->latestOfMany();
    }

    public function events(): HasMany
    {
        return $this->hasMany(MonitoringEvent::class, 'monitoring_mesin_id');
    }

    public function softwareLabel(): string
    {
        return match ($this->software) {
            'onyx' => 'Onyx',
            'flexi' => 'FlexiPRINT',
            'edgeprint' => 'Epson Edge Print',
            'jobcentro' => 'Job Centro',
            'cutter' => 'Cutting Plotter',
            default => 'Lainnya',
        };
    }
}
