@extends('export.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>ID</th>
        <th>Client</th>
        <th>Layanan</th>
        <th>Deskripsi</th>
        <th>Tanggal Mulai</th>
        <th>Tanggal Berhenti</th>
        <th>Catatan</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $index => $item)
        <tr>
          <td align="right">{{ $index + 1 }}</td>
          <td>{{ $item->id }}</td>
          <td>{{ $item->customer->name }} - {{ $item->customer->company }} - {{ $item->customer->address }}</td>
          <td>{{ $item->service->name }}</td>
          <td>{{ $item->description }}</td>
          <td>{{ $item->start_date }}</td>
          <td>{{ $item->end_date }}</td>
          <td>{{ $item->notes }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="8" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
  </table>
@endsection
