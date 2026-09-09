@extends('layouts.app')

@section('content')

<div class="container-fluid">

<div class="card shadow-sm">

<div class="card-header d-flex justify-content-between align-items-center">

<h5 class="mb-0">
<i class="fas fa-magic"></i>
Finishing
</h5>

<button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
Tambah
</button>

</div>

<div class="card-body p-0">

<table class="table table-hover table-sm mb-0">

<thead class="table-light">

<tr>
<th width="50">#</th>
<th>Finishing</th>
<th>Harga Default</th>
<th width="120">Aksi</th>
</tr>

</thead>

<tbody>

@foreach($data as $d)

<tr>

<td>{{ $loop->iteration }}</td>

<td>{{ $d->nama_finishing }}</td>

<td>Rp {{ number_format($d->harga_default) }}</td>

<td>

<form method="POST" action="{{ route('finishing.destroy',$d->id_finishing) }}">
@csrf
@method('DELETE')

<button class="btn btn-danger btn-sm">
Hapus
</button>

</form>

</td>

</tr>

@endforeach

</tbody>

</table>

</div>
</div>
</div>

@endsection