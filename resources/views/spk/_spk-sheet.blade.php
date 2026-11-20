{{--
    Sumber tunggal tubuh SPK Percetakan. Dipakai oleh:
    - spk.print       (satu item/id_detail per dokumen)
    - spk.print-semua (semua item aktif dalam satu nota)
    Variabel wajib: $detail, $finishingItems, $format.
--}}
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
            <tr><td>Ukuran</td><td class="right">{{ angka($detail->p_ukuran, 4) }} x {{ angka($detail->l_ukuran, 4) }} cm</td></tr>
            @endif
            <tr><td>Qty Order</td><td class="right">{{ angka($detail->qty, 4) }}</td></tr>
            <tr><td>Qty Cetak</td><td class="right">{{ $detail->qty_cetak ?? '-' }}</td></tr>
            @foreach($finishingItems as $fin)
            <tr><td colspan="2" style="font-weight:500; padding-top:6px;">Finishing: {{ $fin->nama_finishing }} ({{ $fin->status_finishing }}){{ $fin->metode_hitung === 'quantity' && $fin->qty_pakai ? ' x ' . angka((float) $fin->qty_pakai, 2) : '' }}{{ $fin->catatan ? ' - ' . $fin->catatan : '' }}</td></tr>
            @endforeach
            @if($finishingItems->isEmpty() && ($detail->nama_finishing ?? null))
            <tr><td colspan="2" style="font-weight:500; padding-top:6px;">Finishing: {{ $detail->nama_finishing }}</td></tr>
            @endif
            @if($detail->status_urgent)
            {{-- Tanda urgent dipindah dari "*** TEBAL ***" ke warna kritis
                 OpenCode (var(--color-amber-700)). Pada dokumen yang ditempel di mesin,
                 warna lebih cepat tertangkap daripada huruf tebal, dan
                 tidak perlu tanda bintang yang menambah lebar. --}}
            <tr><td colspan="2" class="right" style="font-weight:500; color:var(--color-amber-700);">URGENT</td></tr>
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
        <div class="center" style="margin: 8px 0; text-align:center;">
            <div id="qr-spk-{{ $detail->id_detail }}" style="display:inline-block"></div>
            {{-- Kode ini dibaca scanner atau diketik ulang manual, jadi
             spacing-nya bukan dekorasi. Diturunkan dari 1px ke 0.5px:
             pada label sepanjang 10px, 1px sudah cukup untuk
             membuat huruf bersenggolan. --}}
        <div style="font-size:10px; letter-spacing:0.5px; margin-top:2px;">TRX{{ $detail->id_transaksi }}</div>
            <div style="font-size:9px; color:var(--color-slate-500);">Scan dengan kamera HP untuk membuka pembayaran</div>
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
