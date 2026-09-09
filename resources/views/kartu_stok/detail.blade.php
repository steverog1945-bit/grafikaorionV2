@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Kartu Stok: {{ $bahan->nama_bahan }}</h4>
    <p>Stok Saat Ini: <strong>{{ number_format($bahan->stok, 2) }}</strong></p>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Tipe</th>
                <th>Qty</th>
                <th>Stok Sebelum</th>
                <th>Stok Sesudah</th>
                <th>Referensi</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kartu as $k)
            <tr>
                <td>{{ $k->tanggal }}</td>
                <td>
                    @if($k->tipe == 'IN')
                        <span class="badge bg-success">IN</span>
                    @elseif($k->tipe == 'OUT')
                        <span class="badge bg-danger">OUT</span>
                    @else
                        <span class="badge bg-warning text-dark">ADJ</span>
                    @endif
                </td>
                <td>{{ number_format($k->qty, 2) }}</td>
                <td>{{ number_format($k->stok_sebelum, 2) }}</td>
                <td>{{ number_format($k->stok_sesudah, 2) }}</td>
                <td>{{ $k->referensi }}</td>
                <td>{{ $k->keterangan }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Belum ada transaksi</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <a href="{{ url('/kartu-stok') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection