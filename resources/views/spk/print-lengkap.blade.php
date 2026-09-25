<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK #{{ $detail->id_detail }} - {{ $detail->nama_produk }}</title>
    @php $appSettingSpk = \App\Models\AppSetting::current(); @endphp
    @include('partials.spk-lengkap-style')
</head>
<body>
    <div class="sheet">
        <div class="topbar">
            <div class="brand">
                @if($appSettingSpk->logo_path)
                    <img src="{{ asset('storage/' . $appSettingSpk->logo_path) }}" alt="Logo">
                @endif
                <div>
                    <strong>{{ $appSettingSpk->nama_aplikasi }}</strong>
                    <span>{{ $detail->divisi ?? 'Produksi' }} &middot; {{ \Carbon\Carbon::parse($detail->tgl_order)->translatedFormat('F Y') }}</span>
                </div>
            </div>
            <div class="doc-badge">
                <span class="doc-label">Surat Perintah Kerja</span>
                <span class="doc-code">SPK #{{ $detail->id_detail }} &middot; {{ $detail->nomor_nota }}</span>
            </div>
        </div>

        <div class="hero">
            <div class="hero-thumb">
                @if($detail->thumbnail)
                    <img src="{{ asset('storage/' . $detail->thumbnail) }}" alt="Gambar desain">
                @else
                    <span class="placeholder">Belum ada gambar desain</span>
                @endif
            </div>
            <div class="hero-info">
                <h1>{{ $detail->nama_file }}</h1>
                <p class="sub">{{ $detail->nama_produk }}@if($detail->status_urgent) &middot; URGENT @endif</p>
                <div class="info-grid">
                    <div class="item"><span>Pemesan</span><strong>{{ $detail->nama_konsumen }}</strong></div>
                    <div class="item"><span>No. Telepon</span><strong>{{ $detail->telepon_konsumen ?: '-' }}</strong></div>
                    <div class="item"><span>Tanggal Masuk</span><strong>{{ \Carbon\Carbon::parse($detail->tgl_order)->format('d/m/Y H:i') }}</strong></div>
                    <div class="item"><span>Desainer / Petugas Input</span><strong>{{ $detail->pembuat_nota ?? '-' }}@if($detail->role_pembuat) <small style="color:#94a3b8;font-weight:600">({{ $detail->role_pembuat }})</small>@endif</strong></div>
                    @if($detail->metode_hitung === 'ukuran')
                    <div class="item"><span>Ukuran Cetak</span><strong>{{ angka($detail->p_ukuran, 2) }} x {{ angka($detail->l_ukuran, 2) }} cm</strong></div>
                    @endif
                    <div class="item"><span>Qty Order</span><strong class="accent">{{ angka($detail->qty, 4) }}</strong></div>
                    <div class="item"><span>Qty Cetak</span><strong>{{ $detail->qty_cetak ?? '-' }}</strong></div>
                    <div class="item"><span>Urgensi</span><strong>{{ $detail->status_urgensi ?? 'Normal' }}</strong></div>
                </div>
            </div>
        </div>

        @if($detail->catatan_produksi)
        <div class="keterangan-box">
            <p>Catatan Produksi</p>
            <pre>{{ $detail->catatan_produksi }}</pre>
        </div>
        @endif

        <div class="section">
            <div class="section-title">Kebutuhan Bahan <span class="note">(dihitung otomatis dari BOM produk, bukan hasil timbang riil)</span></div>
            <table class="material">
                <thead><tr><th>Bahan</th><th style="text-align:right">Pemakaian</th><th style="text-align:right">Perkiraan Panjang Bahan</th></tr></thead>
                <tbody>
                    @forelse($bahan as $b)
                    <tr>
                        <td>{{ $b->nama }}</td>
                        <td class="num">{{ angka($b->qty, 3) }} {{ $b->satuan }}</td>
                        <td class="num">{{ $b->panjang_m !== null ? angka($b->panjang_m, 2) . ' m' : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="empty-note" style="padding: 12px 8px;">Belum ada BOM bahan untuk produk ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Finishing</div>
            <table class="material">
                <thead><tr><th>Finishing</th><th>Spek</th><th style="text-align:right">Jumlah Pakai</th><th>Catatan</th></tr></thead>
                <tbody>
                    @forelse($finishingItems as $fin)
                    <tr>
                        <td>{{ $fin->nama_finishing }}</td>
                        <td>{{ $fin->status_finishing }}</td>
                        <td class="num">{{ $fin->metode_hitung === 'quantity' && $fin->qty_pakai ? angka((float) $fin->qty_pakai, 2) : '-' }}</td>
                        <td>{{ $fin->catatan ?: '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="empty-note" style="padding: 12px 8px;">Tanpa finishing.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin: 8px 0; text-align:center;"><div id="qr-spk" style="display:inline-block"></div><div style="font-size:10px; letter-spacing:1px; margin-top:2px;">TRX{{ $detail->id_transaksi }}</div><div style="font-size:9px; color:#64748b;">Scan dengan kamera HP untuk membuka pembayaran</div></div>

        <div class="footer-strip">
            <span class="dot"></span>
            <span>Wajib tanda tangan di setiap tahap proses dan SPK wajib dikembalikan bersama barangnya.</span>
        </div>

        <div class="signature">
            <div><p class="role">Desainer</p><div class="line"></div></div>
            <div><p class="role">Produksi</p><div class="line"></div></div>
            <div><p class="role">Pemeriksa / QC</p><div class="line"></div></div>
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak SPK</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <script>
        // QR berisi ALAMAT halaman pembayaran nota: dipindai kamera HP biasa langsung membuka form bayar (login dulu bila belum).
        (function () {
            const qr = qrcode(0, 'M');
            qr.addData(@json(\App\Models\AppSetting::urlAkses() . '/kasir/bayar-form/' . $detail->id_transaksi));
            qr.make();
            document.getElementById('qr-spk').innerHTML = qr.createSvgTag({ cellSize: 4, margin: 2, scalable: true });
            const svg = document.querySelector('#qr-spk svg'); if (svg) { svg.style.width = '150px'; svg.style.height = '150px'; }
        })();
    </script>
</body>
</html>
