@extends('export.layout', [
    'title' => $title,
])

@section('content')

  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>ID</th>
        <th>Nama Layanan</th>
        <th>Status</th>
        <th>Catatan</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $index => $item)
        <tr>
          <td align="right">{{ $index + 1 }}</td>
          <td>{{ $item->id }}</td>
          <td>{{ $item->name }}</td>
          <td>{{ $item->active ? 'Aktif' : 'Non Aktif' }}</td>
          <td>{{ $item->notes }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="5" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
  </table>
  @endsection
