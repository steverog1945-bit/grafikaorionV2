<?php

namespace App\Services;

use App\Models\JerseyComponent;
use App\Models\JerseySizeComponentUsage;

/**
 * Estimasi pemakaian bahan (meter) per baris order jersey, dihitung
 * otomatis dari qty per ukuran x matriks estimasi ukuran-komponen yang
 * diisi admin di Master Data (mis. ukuran L component Body = 0.75 meter).
 * Murni ESTIMASI (bukan pemotongan stok riil - lihat catatan migrasi
 * pembuatan matriks ini) - kalau ukuran tertentu belum ada angka
 * estimasinya, baris itu dilewati begitu saja, tidak pernah menggagalkan
 * pembuatan order.
 */
class JerseyMaterialEstimationService
{
    /**
     * @param  array<int,int>  $qtyPerSize  [jersey_size_id => qty]
     * @return array<int,array{component: JerseyComponent, total_meter: float}>
     */
    public static function hitungEstimasi(array $qtyPerSize): array
    {
        $qtyPerSize = array_filter($qtyPerSize, fn ($q) => $q > 0);
        if (empty($qtyPerSize)) {
            return [];
        }

        $usages = JerseySizeComponentUsage::with('component')
            ->whereIn('jersey_size_id', array_keys($qtyPerSize))
            ->whereNotNull('usage_meter')
            ->get();

        $totals = []; // component_id => ['component' => ..., 'total_meter' => ...]

        foreach ($usages as $usage) {
            if (!$usage->component || !$usage->component->is_active) {
                continue;
            }
            $qty = $qtyPerSize[$usage->jersey_size_id] ?? 0;
            if ($qty <= 0) {
                continue;
            }
            $componentId = $usage->jersey_component_id;
            if (!isset($totals[$componentId])) {
                $totals[$componentId] = ['component' => $usage->component, 'total_meter' => 0.0];
            }
            $totals[$componentId]['total_meter'] += (float) $usage->usage_meter * $qty;
        }

        uasort($totals, fn ($a, $b) => $a['component']->sort_order <=> $b['component']->sort_order);

        return array_values($totals);
    }
}
