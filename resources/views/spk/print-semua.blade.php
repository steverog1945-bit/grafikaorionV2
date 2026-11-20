<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Kerja SPK {{ $details->first()->nomor_nota }}</title>
    @include('partials.print-style')
    <style>
        /* Daftar kerja dibaca sambil dibagikan ke tim produksi, bukan
           disimpan - jadi hurufnya dinaikkan dan jaraknya dirapatkan:
           lebih mudah dibaca, tetapi tetap muat banyak item per lembar. */
        .print-header { margin-bottom: 6px; }
        .divider { margin: 3px 0; }
        .table-compact th, .table-compact td { padding: 1.5px 4px; }
        .item-daftar { margin-bottom: 4px; }
        .item-daftar .nama { font-size: 13px; font-weight: 500; }
        .item-daftar .meta { font-size: {{ $format === 'thermal' ? '11.5px' : '12px' }}; color: var(--ink-600); }
        /* Qty dan status menempel pada nama file selama masih muat; bila
           tidak muat ia pindah utuh ke baris berikutnya, tidak terpotong
           di tengah ("qty 1 ·" di ujung baris). */
        .meta-inline {
            font-size: {{ $format === 'thermal' ? '11px' : '11.5px' }};
            font-weight: 400;
            color: var(--ink-600);
            white-space: nowrap;
        }
        .daftar-table th { font-size: 11.5px; }
        .daftar-table td { font-size: 13px; }
        @if($format !== 'thermal')
        /* Daftar kerja A4 selebar kertas (bukan 480px milik struk, dan
           bukan selebar jendela browser - pratinjau harus sama dengan
           hasil cetak). */
        .sheet { max-width: 780px; margin-left: auto; margin-right: auto; }
        table.info-nota { max-width: 380px; margin: 0 auto; }
        @endif
    </style>
</head>
<body>
    @php
        $nota = $details->first();

        // Keterangan desain kadang tersimpan berulang di database (penanda
        // seperti "[FILE SIAP CETAK ...]" tertulis dua kali karena pernah
        // ditambahkan lebih dari sekali). Untuk cetakan, penanda yang sama
        // cukup tampil sekali; isi database tidak diubah.
        $ketDesain = function ($teks) {
            $teks = trim((string) $teks);
            if ($teks === '' || ! preg_match_all('/\[[^\]]+\]/', $teks, $cocok) || count($cocok[0]) < 2) {
                return $teks;
            }
            $sisa = trim(preg_replace('/\[[^\]]+\]/', ' ', $teks));
            return trim(implode(' ', array_unique($cocok[0])) . ($sisa !== '' ? ' ' . $sisa : ''));
        };
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

        {{-- Isi daftar sengaja hanya: nama file/deskripsi, qty, status, dan
             keterangan desain (mis. "FILE SIAP CETAK - dari pelanggan").
             Produk, ukuran, dan finishing bisa dilihat setelah barcode
             discan/dibuka, jadi tidak dicetak di kertas. --}}
        @if($format === 'thermal')
            @foreach($details->values() as $i => $detail)
            <div class="item-daftar">
                <div class="nama">{{ $i + 1 }}. {{ $detail->nama_file }}<span class="meta-inline"> &middot; qty {{ angka($detail->qty, 4) }} &middot; {{ $detail->status_spk }}@if($detail->status_urgent) (URGENT)@endif</span></div>
                @if($ketDesain($detail->catatan_produksi) !== '')
                <div class="meta">Ket: {{ $ketDesain($detail->catatan_produksi) }}</div>
                @endif
            </div>
            @endforeach
        @else
            <table class="table-compact daftar-table">
                <thead>
                    <tr>
                        <th style="width:26px;">No</th>
                        <th style="width:64%;">Nama File / Deskripsi &middot; Qty &middot; Status</th>
                        <th>Keterangan Desain</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($details->values() as $i => $detail)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $detail->nama_file }}<span class="meta-inline"> &middot; qty {{ angka($detail->qty, 4) }} &middot; {{ $detail->status_spk }}@if($detail->status_urgent) (URGENT)@endif</span></td>
                        <td>{{ $ketDesain($detail->catatan_produksi) ?: '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
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
