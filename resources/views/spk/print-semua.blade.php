<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Kerja SPK {{ $details->first()->nomor_nota }}</title>
    @include('partials.print-style')
    @if($format !== 'thermal')
    <style>
        /* Daftar kerja A4 memakai lebar kertas penuh - style nota
           membatasi .sheet ke 480px (pas untuk struk). */
        .sheet { max-width: 100%; }
        table.info-nota { max-width: 380px; margin: 0 auto; }
    </style>
    @endif
</head>
<body>
    @php
        $nota = $details->first();
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
            <div style="margin-bottom: 6px;">
                <div>{{ $i + 1 }}. {{ $detail->nama_file }}</div>
                <div style="font-size:10px; color:var(--ink-600);">
                    qty {{ angka($detail->qty, 4) }} &middot; {{ $detail->status_spk }}@if($detail->status_urgent) (URGENT)@endif
                </div>
                @if($detail->catatan_produksi)
                <div style="font-size:10px; color:var(--ink-600);">Ket: {{ $detail->catatan_produksi }}</div>
                @endif
            </div>
            @endforeach
        @else
            <table class="table-compact">
                <thead>
                    <tr>
                        <th style="width:26px;">No</th>
                        <th>Nama File / Deskripsi</th>
                        <th class="right" style="width:44px;">Qty</th>
                        <th style="width:90px;">Status</th>
                        <th>Keterangan Desain</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($details->values() as $i => $detail)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $detail->nama_file }}</td>
                        <td class="right">{{ angka($detail->qty, 4) }}</td>
                        <td>{{ $detail->status_spk }}@if($detail->status_urgent) (URGENT)@endif</td>
                        <td>{{ $detail->catatan_produksi ?: '-' }}</td>
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
