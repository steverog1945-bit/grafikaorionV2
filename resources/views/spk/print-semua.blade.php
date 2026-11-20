<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Kerja SPK {{ $details->first()->nomor_nota }}</title>
    @include('partials.print-style')
    @if($format !== 'thermal')
    <style>
        /* Daftar kerja A4 harus memakai lebar kertas penuh - style nota
           membatasi .sheet ke 480px (pas untuk struk), terlalu sempit
           untuk tabel banyak kolom. */
        .sheet { max-width: 100%; }
        /* Blok identitas nota tidak perlu melebar sepanjang kertas -
           nilai yang rata kanan jadi jauh dari labelnya. */
        table.info-nota { max-width: 380px; margin: 0 auto; }
    </style>
    @endif
</head>
<body>
    @php
        $nota = $details->first();
        // Data baris disiapkan sekali supaya cabang thermal (daftar linear,
        // 80mm) dan cabang A4 (tabel) membaca isi yang sama persis.
        $barisItem = $details->values()->map(function ($detail, $i) use ($finishingItems) {
            $fin = $finishingItems[$detail->id_detail] ?? collect();
            // Hanya NAMA finishing - status/catatan finishing milik tim
            // finishing dan membuat baris melebar tanpa guna.
            $finText = $fin->pluck('nama_finishing')->implode(', ');
            if ($finText === '' && ($detail->nama_finishing ?? null)) {
                $finText = $detail->nama_finishing;
            }
            $ukuran = ($detail->metode_hitung ?? null) === 'ukuran'
                ? angka($detail->p_ukuran, 2) . ' x ' . angka($detail->l_ukuran, 2)
                : '-';
            return [
                'detail' => $detail,
                'no' => $i + 1,
                'fin' => $finText,
                'ukuran' => $ukuran,
            ];
        });
    @endphp
    <div class="sheet">
        @include('partials.print-header', ['docTitle' => 'Daftar Kerja SPK'])

        {{-- Satu barcode untuk seluruh nota: scan sekali, semua item nota
             langsung terbuka di Monitoring Kasir. --}}
        <div class="center" style="margin: 6px 0 2px;">
            <div id="qr-spk-nota" style="display:inline-block"></div>
            <div style="font-size:10px; letter-spacing:0.5px; margin-top:2px;">TRX{{ $nota->id_transaksi }}</div>
            <div style="font-size:9px; color:var(--color-slate-500);">Scan: seluruh item nota ini tampil</div>
        </div>

        <div class="divider"></div>

        <table class="table-compact info-nota">
            <tr><td>No. Nota</td><td class="right">{{ $nota->nomor_nota }}</td></tr>
            <tr><td>Tanggal Order</td><td class="right">{{ \Carbon\Carbon::parse($nota->tgl_order)->format('d/m/Y H:i') }}</td></tr>
            <tr><td>Konsumen</td><td class="right">{{ $nota->nama_konsumen }}</td></tr>
            <tr><td>Jumlah Item</td><td class="right">{{ $details->count() }}</td></tr>
        </table>

        <div class="divider"></div>

        @if($format === 'thermal')
            {{-- THERMAL 80mm: daftar linear, bukan tabel banyak kolom.
                 Empat kolom pada kertas 80mm memaksa "Catatan" jadi kolom
                 satu kata per baris sehingga daftar lebih panjang daripada
                 struk per item - justru menghemat kertasnya hilang. --}}
            @foreach($barisItem as $b)
            @php $detail = $b['detail']; @endphp
            <div style="margin-bottom: 7px;">
                <div style="font-weight:500;">
                    {{ $b['no'] }}. #{{ $detail->id_detail }} &middot; {{ $detail->nama_file }}
                    @if($detail->status_urgent)<span style="color:var(--color-amber-700);"> (URGENT)</span>@endif
                </div>
                <div style="font-size:10px; color:var(--ink-600);">
                    {{ $detail->nama_produk }}@if(($detail->metode_hitung ?? null) === 'ukuran') &middot; {{ $b['ukuran'] }} cm @endif
                    &middot; qty {{ angka($detail->qty, 4) }}
                    @if($b['fin'] !== '') &middot; {{ $b['fin'] }} @endif
                    &middot; {{ $detail->status_spk }}
                </div>
                @if($detail->catatan_produksi)
                <div style="font-size:10px; color:var(--ink-600);">Catatan: {{ $detail->catatan_produksi }}</div>
                @endif
            </div>
            @endforeach
        @else
            <table class="table-compact">
                <thead>
                    <tr>
                        <th style="width:26px;">No</th>
                        <th style="width:56px;">No. SPK</th>
                        <th style="width:24%;">Nama File / Item</th>
                        <th style="width:13%;">Produk</th>
                        <th style="width:78px;">Ukuran</th>
                        <th class="right" style="width:34px;">Qty</th>
                        <th style="width:13%;">Finishing</th>
                        <th style="width:24%;">Catatan</th>
                        <th style="width:58px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($barisItem as $b)
                    @php $detail = $b['detail']; @endphp
                    <tr>
                        <td>{{ $b['no'] }}</td>
                        <td>#{{ $detail->id_detail }}</td>
                        <td>
                            {{ $detail->nama_file }}
                            @if($detail->status_urgent)<span style="color:var(--color-amber-700);font-weight:500;"> (URGENT)</span>@endif
                        </td>
                        <td>{{ $detail->nama_produk }}</td>
                        <td>{{ $b['ukuran'] }}</td>
                        <td class="right">{{ angka($detail->qty, 4) }}</td>
                        <td>{{ $b['fin'] !== '' ? $b['fin'] : '-' }}</td>
                        <td>{{ $detail->catatan_produksi ?: '-' }}</td>
                        <td>{{ $detail->status_spk }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="signature">
            <div><div class="line">Diambil Oleh</div></div>
            <div><div class="line">Diperiksa Oleh</div></div>
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak Daftar SPK</button>
    </div>

    <script src="{{ asset('vendor/qrcode-generator/qrcode.min.js') }}"></script>
    <script>
        (function () {
            const el = document.getElementById('qr-spk-nota');
            if (!el) return;
            const qr = qrcode(0, 'M');
            qr.addData(@json(\App\Models\AppSetting::urlAkses() . '/kasir/bayar-form/' . $nota->id_transaksi));
            qr.make();
            el.innerHTML = qr.createSvgTag({ cellSize: {{ $format === 'thermal' ? 3 : 4 }}, margin: 2, scalable: true });
            const svg = el.querySelector('svg');
            if (svg) {
                svg.style.width = '{{ $format === 'thermal' ? 110 : 130 }}px';
                svg.style.height = '{{ $format === 'thermal' ? 110 : 130 }}px';
            }
        })();
    </script>
</body>
</html>
