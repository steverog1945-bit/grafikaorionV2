<?php

namespace App\Http\Controllers\Jersey;

use App\Http\Controllers\Controller;
use App\Models\JerseyCategory;
use App\Models\JerseyCollarType;
use App\Models\JerseyComponent;
use App\Models\JerseyDesigner;
use App\Models\JerseyMaterial;
use App\Models\JerseyPriceSetting;
use App\Models\JerseyProductType;
use App\Models\JerseySize;
use App\Models\JerseySizeCollarUsage;
use App\Models\JerseySizeComponentUsage;
use App\Models\JerseySizeSleeveUsage;
use App\Models\JerseySleeveType;
use App\Services\JerseyMaterialEstimationService;
use Illuminate\Http\Request;

/**
 * Semua master data modul jersey (desainer, tipe produk, kategori, ukuran,
 * bahan, estimasi bahan per ukuran, tier harga) dalam SATU halaman bertab.
 * Semua aksi (tambah/ubah/toggle/hapus) dikirim lewat fetch() JSON dan
 * mengembalikan JSON juga - BUKAN submit form biasa - supaya tab yang
 * sedang aktif tidak pernah ke-reset ke tab pertama tiap kali user
 * menambah satu baris data.
 */
class JerseyMasterController extends Controller
{
    public function index()
    {
        $designers = JerseyDesigner::orderBy('name')->get();
        $productTypes = JerseyProductType::orderBy('name')->get();
        $categories = JerseyCategory::orderBy('name')->get();
        $sizes = JerseySize::orderBy('sort_order')->get();
        $materials = JerseyMaterial::orderBy('name')->get();
        $components = JerseyComponent::orderBy('sort_order')->get();
        $sleeveTypes = JerseySleeveType::orderBy('sort_order')->get();
        $collarTypes = JerseyCollarType::orderBy('sort_order')->get();
        $priceSettings = JerseyPriceSetting::with(['productType', 'category', 'material'])
            ->orderByRaw('jersey_material_id IS NULL')
            ->orderBy('jersey_material_id')
            ->orderBy('jersey_category_id')
            ->orderBy('qty_min')
            ->get();

        // Matriks estimasi: [id_ukuran][id_komponen] => {usage_meter, usage_weight_gram}
        $usageMatrix = JerseySizeComponentUsage::all()
            ->groupBy('jersey_size_id')
            ->map(fn ($rows) => $rows->keyBy('jersey_component_id'));

        // Matriks estimasi Lengan & Kerah - struktur sama persis dengan
        // Komponen di atas, tapi tabel & kolom FK-nya beda (lihat
        // JerseyMaterialEstimationService::hitungEstimasiLengan/Kerah()).
        $sleeveUsageMatrix = JerseySizeSleeveUsage::all()
            ->groupBy('jersey_size_id')
            ->map(fn ($rows) => $rows->keyBy('jersey_sleeve_type_id'));
        $collarUsageMatrix = JerseySizeCollarUsage::all()
            ->groupBy('jersey_size_id')
            ->map(fn ($rows) => $rows->keyBy('jersey_collar_type_id'));

        // Total per ukuran (jumlah semua komponen aktif) - ditampilkan di
        // kolom "Total" pada kedua matriks (meter & berat), dihitung ulang
        // secara live lewat JS begitu admin mengubah sel, ini cuma nilai
        // awal saat halaman pertama dimuat.
        $komponenAktif = $components->where('is_active', true);
        $totalPerSize = [];
        foreach ($sizes as $s) {
            $meter = 0.0;
            $gram = 0.0;
            foreach ($komponenAktif as $comp) {
                $usage = optional($usageMatrix[$s->id] ?? null)[$comp->id] ?? null;
                $meter += (float) ($usage->usage_meter ?? 0);
                $gram += (float) ($usage->usage_weight_gram ?? 0);
            }
            $totalPerSize[$s->id] = [
                'meter_text' => $meter > 0 ? number_format($meter, 2, ',', '.') . ' m' : '-',
                'berat_text' => JerseyMaterialEstimationService::formatBeratGram($gram),
            ];
        }

        return view('jersey.master.index', compact(
            'designers', 'productTypes', 'categories', 'sizes', 'materials', 'components', 'sleeveTypes', 'collarTypes',
            'priceSettings', 'usageMatrix', 'sleeveUsageMatrix', 'collarUsageMatrix', 'totalPerSize'
        ));
    }

    // ---- Desainer ----
    public function storeDesigner(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:jersey_designers,name']);
        $row = JerseyDesigner::create($data);
        return response()->json(['success' => true, 'message' => 'Desainer berhasil ditambahkan.', 'row' => $row]);
    }

    public function updateDesigner(Request $request, JerseyDesigner $designer)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:jersey_designers,name,' . $designer->id]);
        $designer->update($data);
        return response()->json(['success' => true, 'message' => 'Desainer berhasil diperbarui.', 'row' => $designer]);
    }

    public function toggleDesigner(JerseyDesigner $designer)
    {
        $designer->update(['is_active' => !$designer->is_active]);
        return response()->json(['success' => true, 'message' => 'Status desainer diperbarui.', 'row' => $designer]);
    }

    // ---- Tipe Produk ----
    public function storeProductType(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:jersey_product_types,name']);
        $data['has_sleeve_collar'] = $request->boolean('has_sleeve_collar', true);
        $row = JerseyProductType::create($data);
        return response()->json(['success' => true, 'message' => 'Tipe produk berhasil ditambahkan.', 'row' => $row]);
    }

    public function updateProductType(Request $request, JerseyProductType $productType)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:jersey_product_types,name,' . $productType->id]);
        $data['has_sleeve_collar'] = $request->boolean('has_sleeve_collar', true);
        $productType->update($data);
        return response()->json(['success' => true, 'message' => 'Tipe produk berhasil diperbarui.', 'row' => $productType]);
    }

    public function toggleProductType(JerseyProductType $productType)
    {
        $productType->update(['is_active' => !$productType->is_active]);
        return response()->json(['success' => true, 'message' => 'Status tipe produk diperbarui.', 'row' => $productType]);
    }

    // ---- Kategori ----
    public function storeCategory(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:jersey_categories,name']);
        $row = JerseyCategory::create($data);
        return response()->json(['success' => true, 'message' => 'Kategori berhasil ditambahkan.', 'row' => $row]);
    }

    public function updateCategory(Request $request, JerseyCategory $category)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:jersey_categories,name,' . $category->id]);
        $category->update($data);
        return response()->json(['success' => true, 'message' => 'Kategori berhasil diperbarui.', 'row' => $category]);
    }

    public function toggleCategory(JerseyCategory $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        return response()->json(['success' => true, 'message' => 'Status kategori diperbarui.', 'row' => $category]);
    }

    // ---- Ukuran ----
    public function storeSize(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:20|unique:jersey_sizes,name',
            'sort_order' => 'nullable|integer|min:0',
            'price_surcharge' => 'nullable|numeric|min:0',
        ]);
        $row = JerseySize::create([
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
            'price_surcharge' => $data['price_surcharge'] ?? 0,
        ]);
        return response()->json(['success' => true, 'message' => 'Ukuran berhasil ditambahkan.', 'row' => $row]);
    }

    public function updateSize(Request $request, JerseySize $size)
    {
        $data = $request->validate([
            'name' => 'required|string|max:20|unique:jersey_sizes,name,' . $size->id,
            'sort_order' => 'nullable|integer|min:0',
            'price_surcharge' => 'nullable|numeric|min:0',
        ]);
        $size->update([
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
            'price_surcharge' => $data['price_surcharge'] ?? 0,
        ]);
        return response()->json(['success' => true, 'message' => 'Ukuran berhasil diperbarui.', 'row' => $size]);
    }

    public function toggleSize(JerseySize $size)
    {
        $size->update(['is_active' => !$size->is_active]);
        return response()->json(['success' => true, 'message' => 'Status ukuran diperbarui.', 'row' => $size]);
    }

    // ---- Bahan ----
    public function storeMaterial(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:jersey_materials,name']);
        $row = JerseyMaterial::create($data);
        return response()->json(['success' => true, 'message' => 'Bahan berhasil ditambahkan.', 'row' => $row]);
    }

    public function updateMaterial(Request $request, JerseyMaterial $material)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:jersey_materials,name,' . $material->id]);
        $material->update($data);
        return response()->json(['success' => true, 'message' => 'Bahan berhasil diperbarui.', 'row' => $material]);
    }

    public function toggleMaterial(JerseyMaterial $material)
    {
        $material->update(['is_active' => !$material->is_active]);
        return response()->json(['success' => true, 'message' => 'Status bahan diperbarui.', 'row' => $material]);
    }

    // ---- Komponen (untuk matriks estimasi bahan per ukuran) ----
    public function storeComponent(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:jersey_components,name',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $row = JerseyComponent::create($data);
        return response()->json(['success' => true, 'message' => 'Komponen berhasil ditambahkan.', 'row' => $row]);
    }

    public function toggleComponent(JerseyComponent $component)
    {
        $component->update(['is_active' => !$component->is_active]);
        return response()->json(['success' => true, 'message' => 'Status komponen diperbarui.', 'row' => $component]);
    }

    // ---- Matriks estimasi bahan (ukuran x komponen -> meter) ----
    public function saveUsage(Request $request)
    {
        $data = $request->validate([
            'jersey_size_id' => 'required|exists:jersey_sizes,id',
            'jersey_component_id' => 'required|exists:jersey_components,id',
            'usage_meter' => 'nullable|numeric|min:0',
            'usage_weight_gram' => 'nullable|numeric|min:0',
        ]);

        // Request cuma mengirim SATU field yang baru diubah (meter ATAU
        // berat, lihat simpanEstimasi() di JS) - hanya masukkan field yang
        // benar-benar dikirim ke $values supaya updateOrCreate() tidak diam-
        // diam menimpa field lain jadi NULL.
        $values = [];
        if ($request->has('usage_meter')) {
            $values['usage_meter'] = $data['usage_meter'] ?? null;
        }
        if ($request->has('usage_weight_gram')) {
            $values['usage_weight_gram'] = $data['usage_weight_gram'] ?? null;
        }

        $row = JerseySizeComponentUsage::updateOrCreate(
            ['jersey_size_id' => $data['jersey_size_id'], 'jersey_component_id' => $data['jersey_component_id']],
            $values
        );

        return response()->json(['success' => true, 'message' => 'Estimasi tersimpan.', 'row' => $row]);
    }

    // ---- Tipe Lengan ----
    public function storeSleeveType(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:jersey_sleeve_types,name',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $row = JerseySleeveType::create($data);
        return response()->json(['success' => true, 'message' => 'Tipe lengan berhasil ditambahkan.', 'row' => $row]);
    }

    public function updateSleeveType(Request $request, JerseySleeveType $sleeveType)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:jersey_sleeve_types,name,' . $sleeveType->id,
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $sleeveType->update($data);
        return response()->json(['success' => true, 'message' => 'Tipe lengan berhasil diperbarui.', 'row' => $sleeveType]);
    }

    public function toggleSleeveType(JerseySleeveType $sleeveType)
    {
        $sleeveType->update(['is_active' => !$sleeveType->is_active]);
        return response()->json(['success' => true, 'message' => 'Status tipe lengan diperbarui.', 'row' => $sleeveType]);
    }

    // ---- Tipe Kerah ----
    public function storeCollarType(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:jersey_collar_types,name',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $row = JerseyCollarType::create($data);
        return response()->json(['success' => true, 'message' => 'Tipe kerah berhasil ditambahkan.', 'row' => $row]);
    }

    public function updateCollarType(Request $request, JerseyCollarType $collarType)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:jersey_collar_types,name,' . $collarType->id,
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $collarType->update($data);
        return response()->json(['success' => true, 'message' => 'Tipe kerah berhasil diperbarui.', 'row' => $collarType]);
    }

    public function toggleCollarType(JerseyCollarType $collarType)
    {
        $collarType->update(['is_active' => !$collarType->is_active]);
        return response()->json(['success' => true, 'message' => 'Status tipe kerah diperbarui.', 'row' => $collarType]);
    }

    // ---- Matriks estimasi Lengan (ukuran x tipe lengan -> meter/berat) ----
    public function saveSleeveUsage(Request $request)
    {
        $data = $request->validate([
            'jersey_size_id' => 'required|exists:jersey_sizes,id',
            'jersey_sleeve_type_id' => 'required|exists:jersey_sleeve_types,id',
            'usage_meter' => 'nullable|numeric|min:0',
            'usage_weight_gram' => 'nullable|numeric|min:0',
        ]);

        $values = [];
        if ($request->has('usage_meter')) {
            $values['usage_meter'] = $data['usage_meter'] ?? null;
        }
        if ($request->has('usage_weight_gram')) {
            $values['usage_weight_gram'] = $data['usage_weight_gram'] ?? null;
        }

        $row = JerseySizeSleeveUsage::updateOrCreate(
            ['jersey_size_id' => $data['jersey_size_id'], 'jersey_sleeve_type_id' => $data['jersey_sleeve_type_id']],
            $values
        );

        return response()->json(['success' => true, 'message' => 'Estimasi lengan tersimpan.', 'row' => $row]);
    }

    // ---- Matriks estimasi Kerah (ukuran x tipe kerah -> meter/berat) ----
    public function saveCollarUsage(Request $request)
    {
        $data = $request->validate([
            'jersey_size_id' => 'required|exists:jersey_sizes,id',
            'jersey_collar_type_id' => 'required|exists:jersey_collar_types,id',
            'usage_meter' => 'nullable|numeric|min:0',
            'usage_weight_gram' => 'nullable|numeric|min:0',
        ]);

        $values = [];
        if ($request->has('usage_meter')) {
            $values['usage_meter'] = $data['usage_meter'] ?? null;
        }
        if ($request->has('usage_weight_gram')) {
            $values['usage_weight_gram'] = $data['usage_weight_gram'] ?? null;
        }

        $row = JerseySizeCollarUsage::updateOrCreate(
            ['jersey_size_id' => $data['jersey_size_id'], 'jersey_collar_type_id' => $data['jersey_collar_type_id']],
            $values
        );

        return response()->json(['success' => true, 'message' => 'Estimasi kerah tersimpan.', 'row' => $row]);
    }

    // ---- Tier Harga ----
    public function storePriceSetting(Request $request)
    {
        $data = $request->validate([
            'jersey_product_type_id' => 'nullable|exists:jersey_product_types,id',
            'jersey_category_id' => 'nullable|exists:jersey_categories,id',
            'jersey_material_id' => 'nullable|exists:jersey_materials,id',
            'qty_min' => 'required|integer|min:1',
            'qty_max' => 'nullable|integer|gte:qty_min',
            'price' => 'required|numeric|min:0',
        ]);
        $row = JerseyPriceSetting::create($data)->load(['productType', 'category', 'material']);
        return response()->json(['success' => true, 'message' => 'Tier harga berhasil ditambahkan.', 'row' => $row]);
    }

    public function updatePriceSetting(Request $request, JerseyPriceSetting $priceSetting)
    {
        $data = $request->validate([
            'jersey_product_type_id' => 'nullable|exists:jersey_product_types,id',
            'jersey_category_id' => 'nullable|exists:jersey_categories,id',
            'jersey_material_id' => 'nullable|exists:jersey_materials,id',
            'qty_min' => 'required|integer|min:1',
            'qty_max' => 'nullable|integer|gte:qty_min',
            'price' => 'required|numeric|min:0',
        ]);
        $priceSetting->update($data);
        $row = $priceSetting->fresh()->load(['productType', 'category', 'material']);
        return response()->json(['success' => true, 'message' => 'Tier harga berhasil diperbarui.', 'row' => $row]);
    }

    public function destroyPriceSetting(JerseyPriceSetting $priceSetting)
    {
        $priceSetting->delete();
        return response()->json(['success' => true, 'message' => 'Tier harga berhasil dihapus.']);
    }
}
