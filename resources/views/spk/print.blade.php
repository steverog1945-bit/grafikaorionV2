<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK #{{ $detail->id_detail }}</title>
    @include('partials.print-style')
</head>
<body>
    <div class="sheet">
        @include('partials.print-header', ['docTitle' => 'Surat Perintah Kerja (SPK)'])

        <table>
            <tr><td>No. SPK</td><td class="right">#{{ $detail->id_detail }}</td></tr>
            <tr><td>No. Nota</td><td class="right">{{ $detail->nomor_nota }}</td></tr>
            <tr><td>Tanggal Order</td><td class="right">{{ \Carbon\Carbon::parse($detail->tgl_order)->format('d/m/Y H:i') }}</td></tr>
            <tr><td>Konsumen</td><td class="right">{{ $detail->nama_konsumen }}</td></tr>
            <tr><td>Divisi/Mesin</td><td class="right">{{ $detail->divisi ?? '-' }}</td></tr>
        </table>

        <div class="divider"></div>

        <table>
            <tr><td>Nama File</td><td class="right">{{ $detail->nama_file }}</td></tr>
            <tr><td>Produk</td><td class="right">{{ $detail->nama_produk }}</td></tr>
            @if($detail->metode_hitung === 'ukuran')
            <tr><td>Ukuran</td><td class="right">{{ $detail->p_ukuran }} x {{ $detail->l_ukuran }} cm</td></tr>
            @endif
            <tr><td>Qty Order</td><td class="right">{{ $detail->qty }}</td></tr>
            <tr><td>Qty Cetak</td><td class="right">{{ $detail->qty_cetak ?? '-' }}</td></tr>
            @if($detail->nama_finishing)
            <tr><td colspan="2" style="font-weight:bold; padding-top:6px;">Finishing: {{ $detail->nama_finishing }}</td></tr>
            @endif
            @if($detail->status_urgent)
            <tr><td colspan="2" class="right" style="font-weight:bold;">*** URGENT ***</td></tr>
            @endif
        </table>

        <div class="divider"></div>

        <p class="center">
            Status: <span class="status-badge">{{ strtoupper($detail->status_spk) }}</span>
        </p>

        {{-- Barcode Code128 berisi "TRX" + id_transaksi (BUKAN id_detail/
             nomor_nota) - begitu discan di Monitoring Kasir, kode ini
             langsung membawa kasir ke halaman Bayar transaksi terkait
             (route kasir.bayarForm sudah menerima id_transaksi apa adanya,
             tidak perlu endpoint pencarian baru). Dirender client-side
             (JsBarcode) supaya tidak perlu tambahan library PHP baru di
             composer. --}}
        <div class="center" style="margin: 8px 0;">
            <svg id="barcode-spk"></svg>
        </div>

        @if($detail->catatan_produksi)
        <div class="catatan">
            <strong>Catatan Produksi:</strong><br>
            {{ $detail->catatan_produksi }}
        </div>
        @endif

        @if($format !== 'thermal')
        <div class="signature">
            <div>
                <div class="line">Diambil Oleh</div>
            </div>
            <div>
                <div class="line">Diperiksa Oleh</div>
            </div>
        </div>
        @endif
    </div>

    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script>
        JsBarcode("#barcode-spk", "TRX{{ $detail->id_transaksi }}", {
            format: "CODE128",
            width: {{ $format === 'thermal' ? 1.3 : 1.8 }},
            height: {{ $format === 'thermal' ? 40 : 50 }},
            fontSize: {{ $format === 'thermal' ? 10 : 12 }},
            margin: 4,
            displayValue: true,
        });
    </script>
</body>
</html>
