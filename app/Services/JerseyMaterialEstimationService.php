<?php

namespace App\Services;

use App\Models\JerseyComponent;
use App\Models\JerseySizeComponentUsage;

/**
 * Estimasi pemakaian bahan (meter & berat) per baris order jersey, dihitung
 * otomatis dari qty per ukuran x matriks estimasi ukuran-komponen yang
 * diisi admin di Master Data (mis. ukuran L component Body = 0.75 meter,
 * 180 gram). Murni ESTIMASI (bukan pemotongan stok riil - lihat catatan
 * migrasi pembuatan matriks ini) - kalau ukuran tertentu belum ada angka
 * estimasinya, baris itu dilewati begitu saja, tidak pernah menggagalkan
 * pembuatan order.
 */
class JerseyMaterialEstimationService
{
    /**
     * @param  array<int,int>  $qtyPerSize  [jersey_size_id => qty]
     * @return array<int,array{component: JerseyComponent, total_meter: float, total_weight_gram: float}>
     */
    public static function hitungEstimasi(array $qtyPerSize): array
    {
        $qtyPerSize = array_filter($qtyPerSize, fn ($q) => $q > 0);
        if (empty($qtyPerSize)) {
            return [];
        }

        $usages = JerseySizeComponentUsage::with('component')
            ->whereIn('jersey_size_id', array_keys($qtyPerSize))
            ->where(function ($q) {
                $q->whereNotNull('usage_meter')->orWhereNotNull('usage_weight_gram');
            })
            ->get();

        $totals = []; // component_id => ['component' => ..., 'total_meter' => ..., 'total_weight_gram' => ...]

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
                $totals[$componentId] = ['component' => $usage->component, 'total_meter' => 0.0, 'total_weight_gram' => 0.0];
            }
            $totals[$componentId]['total_meter'] += (float) $usage->usage_meter * $qty;
            $totals[$componentId]['total_weight_gram'] += (float) $usage->usage_weight_gram * $qty;
        }

        uasort($totals, fn ($a, $b) => $a['component']->sort_order <=> $b['component']->sort_order);

        return array_values($totals);
    }

    /**
     * Format gram jadi teks siap tampil - otomatis pakai kg kalau totalnya
     * >= 1000 gram, supaya angka besar tidak ditampilkan mentah dalam gram
     * (mis. "2500 g" jadi "2,50 kg"). Satu fungsi ini dipakai di mana pun
     * berat perlu ditampilkan (Master Data & cetak ringkasan) supaya
     * aturannya konsisten di satu tempat.
     */
    public static function formatBeratGram(float $gram): string
    {
        if ($gram <= 0) {
            return '-';
        }
        if ($gram >= 1000) {
            return number_format($gram / 1000, 2, ',', '.') . ' kg';
        }
        return number_format($gram, 0, ',', '.') . ' g';
    }
}
