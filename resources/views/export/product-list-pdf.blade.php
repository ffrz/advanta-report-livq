@extends('export.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Kategori</th>
        <th>Nama</th>
        <th>Harga Distributor (Rp / sat)</th>
        <th>Harga (Rp / sat)</th>
        <th>Status</th>
        <th>Catatan</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $index => $item)
        <tr>
          <td align="right">{{ $index + 1 }}</td>
          <td>{{ $item->category ? $item->category->name : '' }}</td>
          <td>{{ $item->name }}</td>
          <td align="right">{{ format_number($item->price_1) }} / {{ $item->uom_1 }}</td>
          <td align="right">{{ format_number($item->price_2) }} / {{ $item->uom_2 }}</td>
          <td align="center">{{ $item->active ? 'Aktif' : 'Non Aktif' }}</td>
          <td>{{ $item->notes }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="7" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
  </table>
@endsection
