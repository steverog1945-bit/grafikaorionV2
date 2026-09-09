@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Daftar Kartu Stok</h4>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Bahan</th>
                <th>Stok Saat Ini</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bahan as $b)
            <tr>
                <td>{{ $b->kode_bahan }}</td>
                <td>{{ $b->nama_bahan }}</td>
                <td>{{ number_format($b->stok, 2) }}</td>
                <td>
                    <a href="{{ url('/kartu-stok/'.$b->id_bahan) }}" class="btn btn-primary btn-sm">
                        Lihat Kartu
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection