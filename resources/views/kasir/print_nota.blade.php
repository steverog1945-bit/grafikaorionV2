<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota {{ $transaksi->nomor_nota }}</title>
    @include('partials.print-style')
</head>
<body>
    <div class="sheet">
        @include('partials.print-header', ['docTitle' => 'Nota Penjualan'])

        <table>
            <tr><td>No. Nota</td><td class="right">{{ $transaksi->nomor_nota }}</td></tr>
            <tr><td>Tanggal</td><td class="right">{{ \Carbon\Carbon::parse($transaksi->tgl_order)->format('d/m/Y H:i') }}</td></tr>
            <tr><td>Konsumen</td><td class="right">{{ $transaksi->nama_konsumen }}</td></tr>
            <tr><td>Kasir</td><td class="right">{{ $transaksi->nama_kasir ?? '-' }}</td></tr>
        </table>

        <div class="divider"></div>

        <table>
            <thead>
                <tr><th>Item</th><th class="right">Qty</th><th class="right">Subtotal</th></tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->nama_file }}</td>
                    <td class="right">{{ $item->qty }}</td>
                    <td class="right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <table>
            <tr class="total-row"><td>TOTAL</td><td class="right" colspan="2">Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</td></tr>
            <tr><td>Terbayar</td><td class="right" colspan="2">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</td></tr>
            <tr><td>Status</td><td class="right" colspan="2">{{ $transaksi->status_pembayaran }}</td></tr>
        </table>

        @php($footerNota = \App\Models\AppSetting::current()->nota_footer_text)
        @if($footerNota)
            <div class="divider"></div>
            <p class="footer-note" style="white-space: pre-line; text-align: left;">{{ $footerNota }}</p>
        @else
            <p class="footer-note">Terima kasih atas kepercayaan Anda</p>
        @endif
    </div>

    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak</button>
    </div>
</body>
</html>
