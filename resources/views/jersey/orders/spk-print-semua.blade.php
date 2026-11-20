<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Kerja SPK {{ $order->order_number }}</title>
    @include('partials.spk-print-style')
</head>
<body>
    @php
        $appSettingSpk = \App\Models\AppSetting::current();
        $totalQty = $lines->sum(fn ($l) => (float) $l->qty);
    @endphp
    <div class="sheet">
        <div class="topbar">
            <div class="brand">
                @if($appSettingSpk->logo_path)
                    <img src="{{ asset('storage/' . $appSettingSpk->logo_path) }}" alt="Logo">
                @endif
                <div>
                    <strong>{{ $appSettingSpk->nama_aplikasi }}</strong>
                    <span>Periode {{ \Carbon\Carbon::parse($order->order_date)->translatedFormat('F Y') }}</span>
                </div>
            </div>
            <div class="doc-badge">
                <span class="doc-label">Daftar Kerja SPK</span>
                <span class="doc-code">{{ $order->order_number }}</span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Ringkasan Order</div>
            <div class="info-grid">
                <div class="item"><span>Pemesan</span><strong>{{ $order->konsumen->nama_konsumen ?? $order->customer_name }}</strong></div>
                <div class="item"><span>Tim</span><strong>{{ $order->team_name ?? '-' }}</strong></div>
                <div class="item"><span>Tanggal Masuk</span><strong>{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</strong></div>
                <div class="item"><span>Tanggal Beres</span><strong>{{ $order->deadline ? \Carbon\Carbon::parse($order->deadline)->format('d/m/Y') : '-' }}</strong></div>
                <div class="item"><span>Desainer</span><strong>{{ $order->desainerNama ?? '-' }}</strong></div>
                <div class="item"><span>Total</span><strong class="accent">{{ $lines->count() }} barang &middot; {{ angka($totalQty, 4) }} pcs</strong></div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Daftar Barang <span class="note">detail gambar &amp; estimasi bahan ada di SPK per barang</span></div>
            <table class="roster">
                <thead>
                    <tr><th>No</th><th>Barang</th><th>Spesifikasi</th><th>Ukuran</th><th class="num">Qty</th><th>Keterangan</th></tr>
                </thead>
                <tbody>
                    @foreach($lines as $i => $baris)
                    @php
                        $ukuranRingkas = $baris->sizeQuantities
                            ->filter(fn ($sq) => $sq->quantity > 0)
                            ->sortBy(fn ($sq) => $sq->size->sort_order ?? 0)
                            ->map(fn ($sq) => ($sq->size->name ?? '-') . ': ' . $sq->quantity)
                            ->implode(' · ');
                        $spek = collect([
                            $baris->productType->name ?? null,
                            $baris->category->name ?? null,
                            $baris->material->name ?? $baris->bahan,
                            $baris->sleeveType->name ?? null,
                            $baris->collarType->name ?? null,
                        ])->filter()->implode(' · ');
                    @endphp
                    <tr>
                        <td class="num">{{ $i + 1 }}</td>
                        <td>
                            {{ $baris->nama_barang }}
                            @if($baris->is_susulan)<span class="note" style="font-size:11px;"> (susulan)</span>@endif
                        </td>
                        <td>{{ $spek !== '' ? $spek : '-' }}</td>
                        <td>{{ $ukuranRingkas !== '' ? $ukuranRingkas : '-' }}</td>
                        <td class="num">{{ angka($baris->qty, 4) }}</td>
                        <td>{{ \Illuminate\Support\Str::limit((string) $baris->keterangan, 150) ?: '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @foreach($lines as $baris)
            @if($baris->items->isNotEmpty())
            <div class="section">
                <div class="section-title">Nama &amp; Nomor Punggung <span class="note">{{ $baris->nama_barang }}</span></div>
                <table class="roster">
                    <thead>
                        <tr><th>No</th><th>Nama</th><th>No. Punggung</th><th>Ukuran</th><th>Gender</th><th>Lengan</th><th>Kerah</th></tr>
                    </thead>
                    <tbody>
                        @php
                            $itemsSorted = $baris->items->sortBy([
                                ['sort_size_order', 'asc'],
                                ['player_name', 'asc'],
                            ], SORT_NATURAL | SORT_FLAG_CASE)->values();
                        @endphp
                        @foreach($itemsSorted as $i => $item)
                        <tr>
                            <td class="num">{{ $i + 1 }}</td>
                            <td>{{ $item->player_name }}</td>
                            <td>{{ $item->player_number ?? '-' }}</td>
                            <td>{{ $item->size }}</td>
                            <td>{{ $item->gender }}</td>
                            <td>{{ $item->sleeve }}</td>
                            <td>{{ $item->collar ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        @endforeach

        <div class="footer-strip">
            <span class="dot"></span>
            <span>Daftar ini untuk pembagian kerja. SPK per barang tetap dicetak bila perlu ditempelkan pada barang.</span>
        </div>

        <div class="signature">
            <div><p class="role">Penerima</p><div class="line"></div></div>
            <div><p class="role">{{ $appSettingSpk->nama_aplikasi }}</p><div class="line"></div></div>
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()">Cetak Daftar SPK</button>
    </div>
</body>
</html>
