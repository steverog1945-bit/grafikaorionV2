{{-- Portal lacak pelanggan: mandiri tanpa layouts.app (halaman publik),
    read-only. Gerbang: nomor nota + nomor HP harus cocok dengan data nota. --}}
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Lacak Pesanan - Orion Grafika</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        background: #f1f5f9; color: #0f172a; min-height: 100vh;
        display: flex; flex-direction: column; align-items: center;
        padding: 24px 16px 48px;
    }
    .kartu { width: 100%; max-width: 640px; }
    .brand { text-align: center; margin-bottom: 20px; }
    .brand h1 { font-size: 20px; font-weight: 700; color: #1e293b; }
    .brand p { font-size: 13px; color: #64748b; margin-top: 4px; }
    .panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; margin-bottom: 16px; }
    label { display: block; font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #64748b; margin-bottom: 6px; }
    input[type=text], input[type=tel] {
        width: 100%; border: 1px solid #cbd5e1; border-radius: 10px;
        padding: 10px 14px; font-size: 15px; color: #0f172a; background: #f8fafc;
    }
    input:focus { outline: none; border-color: #6366f1; background: #fff; }
    .grup { margin-bottom: 14px; }
    button {
        width: 100%; border: 0; border-radius: 10px; padding: 12px 16px;
        font-size: 14px; font-weight: 600; color: #fff; background: #4f46e5; cursor: pointer;
    }
    button:hover { background: #4338ca; }
    .galat {
        background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c;
        border-radius: 10px; padding: 10px 14px; font-size: 13px; margin-bottom: 14px;
    }
    .kbar { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .badge { font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 999px; }
    .b-netral { background: #e2e8f0; color: #334155; }
    .b-indigo { background: #e0e7ff; color: #4338ca; }
    .b-amber { background: #fef3c7; color: #92400e; }
    .b-emas { background: #dcfce7; color: #166534; }
    .b-merah { background: #fee2e2; color: #b91c1c; }
    h2 { font-size: 13px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #475569; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th { text-align: left; font-size: 10px; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
    td { padding: 8px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
    .dl { display: grid; grid-template-columns: max-content 1fr; gap: 4px 16px; font-size: 14px; }
    .dl dt { color: #64748b; font-size: 12px; padding-top: 2px; }
    .dl dd { font-weight: 600; color: #1e293b; }
    .uang { text-align: right; white-space: nowrap; }
    .catatan { font-size: 11px; color: #94a3b8; text-align: center; margin-top: 12px; line-height: 1.5; }
</style>
</head>
<body>
<div class="kartu">
    <div class="brand">
        <h1>Lacak Pesanan</h1>
        <p>Masukkan nomor nota dan nomor HP yang terdaftar saat pemesanan.</p>
    </div>

    @if (session('lacakError'))
    <div class="panel">
        <div class="galat">{{ session('lacakError') }}</div>
    </div>
    @endif

    @if (!isset($nota))
    <div class="panel">
        <form method="POST" action="{{ route('lacak.cari') }}">
            @csrf
            <div class="grup">
                <label for="nomor_nota">Nomor Nota</label>
                <input id="nomor_nota" type="text" name="nomor_nota" value="{{ old('nomor_nota') }}" required maxlength="40" placeholder="contoh: NT-2026-0231" autofocus>
            </div>
            <div class="grup">
                <label for="telepon">Nomor HP</label>
                <input id="telepon" type="tel" name="telepon" value="{{ old('telepon') }}" required maxlength="24" placeholder="contoh: 0812xxxxxxx">
            </div>
            <button type="submit">Lacak Pesanan</button>
        </form>
    </div>
    @endif

    @isset($nota)
    <div class="panel">
        <div class="kbar" style="margin-bottom:12px">
            <span style="font-size:16px;font-weight:700">{{ $nota->nomor_nota }}</span>
            <span class="badge {{ ['Dibatalkan' => 'b-merah', 'Selesai' => 'b-emas'][$nota->status_nota] ?? 'b-netral' }}">{{ $nota->status_nota }}</span>
            <span class="badge {{ ['Lunas' => 'b-emas', 'DP' => 'b-indigo', 'Belum Bayar' => 'b-amber', 'Angsuran' => 'b-amber'][$nota->status_pembayaran] ?? 'b-netral' }}">{{ $nota->status_pembayaran }}</span>
        </div>
        <dl class="dl">
            <dt>Pemesan</dt><dd>{{ $nota->nama_konsumen }}</dd>
            <dt>Tanggal Order</dt><dd>{{ \Carbon\Carbon::parse($nota->tgl_order)->translatedFormat('d M Y') }}</dd>
            <dt>Jenis Order</dt><dd>{{ $nota->tipe_order }}</dd>
            <dt>Status Produksi</dt><dd>{{ $nota->status_order }}</dd>
            @if (trim((string) $nota->status_collecting) !== '')
            <dt>Pengambilan</dt><dd>{{ $nota->status_collecting }}{{ $nota->tgl_collecting ? ' - ' . \Carbon\Carbon::parse($nota->tgl_collecting)->translatedFormat('d M Y') : '' }}</dd>
            @endif
        </dl>
    </div>

    <div class="panel">
        <h2>Rincian Pekerjaan</h2>
        <table>
            <thead><tr><th>Barang</th><th>Qty</th><th>Status SPK</th></tr></thead>
            <tbody>
                @forelse ($item as $it)
                <tr>
                    <td>
                        {{ $it->nama_file }}
                        @if (trim((string) $it->p_ukuran) !== '' || trim((string) $it->l_ukuran) !== '')
                        <span style="color:#94a3b8;font-size:11px"> &middot; {{ $it->p_ukuran }} x {{ $it->l_ukuran }}</span>
                        @endif
                    </td>
                    <td class="uang">{{ (int) $it->qty }}</td>
                    <td><span class="badge {{ ['Antre' => 'b-amber', 'Desain' => 'b-indigo', 'Cetak' => 'b-indigo', 'Selesai' => 'b-emas'][$it->status_spk] ?? 'b-netral' }}">{{ $it->status_spk }}</span></td>
                </tr>
                @empty
                <tr><td colspan="3" style="color:#94a3b8">Belum ada rincian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="panel">
        <h2>Pembayaran</h2>
        <dl class="dl">
            <dt>Total Tagihan</dt><dd>Rp {{ number_format($total, 0, ',', '.') }}</dd>
            <dt>Sudah Dibayar</dt><dd>Rp {{ number_format($dibayar, 0, ',', '.') }}</dd>
            <dt>Sisa</dt><dd style="{{ $sisa > 0.01 ? 'color:#b45309' : 'color:#166534' }}">Rp {{ number_format($sisa, 0, ',', '.') }}</dd>
            @if (trim((string) $nota->metode_pembayaran) !== '')
            <dt>Metode Terakhir</dt><dd>{{ $nota->metode_pembayaran }}</dd>
            @endif
        </dl>
    </div>
    @endisset

    <div class="panel" style="background:#f8fafc">
        <form method="POST" action="{{ route('lacak.cari') }}">
            @csrf
            <div class="kbar">
                <input type="text" name="nomor_nota" value="{{ old('nomor_nota', $nota->nomor_nota ?? '') }}" required maxlength="40" placeholder="Nomor nota lain" style="flex:1 1 200px">
                <input type="tel" name="telepon" value="{{ old('telepon') }}" required maxlength="24" placeholder="Nomor HP" style="flex:1 1 160px">
                <button type="submit" style="width:auto;flex:0 0 auto;padding:10px 18px">Cari</button>
            </div>
        </form>
    </div>

    <p class="catatan">
        Halaman ini hanya menampilkan progres pesanan, bukan dokumen resmi pembayaran.<br>
        Ada pertanyaan? Hubungi kasir Orion Grafika dengan menyebut nomor nota Anda.
    </p>
</div>
</body>
</html>
