<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Kerja SPK {{ $details->first()->nomor_nota }}</title>
    @include('partials.print-style')
    @if($format !== 'thermal')
    <style>
        /* Daftar kerja harus memakai lebar kertas penuh - style nota
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
        $judul = 'Daftar Kerja SPK';
    @endphp
    <div class="sheet">
        @include('partials.print-header', ['docTitle' => $judul])

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

        <table class="table-compact">
            <thead>
                <tr>
                    <th style="width:26px;">No</th>
                    <th style="width:56px;">No. SPK</th>
                    <th style="width:24%;">Nama File / Item</th>
                    @if($format !== 'thermal')
                    <th style="width:13%;">Produk</th>
                    <th style="width:78px;">Ukuran</th>
                    @endif
                    <th class="right" style="width:34px;">Qty</th>
                    @if($format !== 'thermal')
                    <th style="width:13%;">Finishing</th>
                    @endif
                    <th style="width:24%;">Catatan</th>
                    <th style="width:58px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($details as $i => $detail)
                @php
                    $fin = $finishingItems[$detail->id_detail] ?? collect();
                    // Hanya NAMA finishing - status/catatan finishing milik
                    // tim finishing dan membuat kolom ini melebar tanpa guna.
                    $finText = $fin->pluck('nama_finishing')->implode(', ');
                    if ($finText === '' && ($detail->nama_finishing ?? null)) {
                        $finText = $detail->nama_finishing;
                    }
                    $ukuran = ($detail->metode_hitung ?? null) === 'ukuran'
                        ? angka($detail->p_ukuran, 2) . ' x ' . angka($detail->l_ukuran, 2)
                        : '-';
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>#{{ $detail->id_detail }}</td>
                    <td>
                        {{ $detail->nama_file }}
                        @if($detail->status_urgent)<span style="color:var(--color-amber-700);font-weight:500;"> (URGENT)</span>@endif
                    </td>
                    @if($format !== 'thermal')
                    <td>{{ $detail->nama_produk }}</td>
                    <td>{{ $ukuran }}</td>
                    @endif
                    <td class="right">{{ angka($detail->qty, 4) }}</td>
                    @if($format !== 'thermal')
                    <td>{{ $finText !== '' ? $finText : '-' }}</td>
                    @endif
                    <td>{{ $detail->catatan_produksi ?: '-' }}</td>
                    <td>{{ $detail->status_spk }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

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
