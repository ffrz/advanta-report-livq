@extends('export.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>ID</th>
        <th>Tanggal</th>
        <th>Sales</th>
        <th>Client</th>
        <th>Layanan</th>
        <th>Deskripsi</th>
        <th>Jumlah</th>
        <th>Catatan</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $index => $item)
        <tr>
          <td align="right">{{ $index + 1 }}</td>
          <td>{{ $item->id }}</td>
          <td>{{ $item->date }}</td>
          <td>{{ $item->user->name }} ({{ $item->user->username }})</td>
          <td>
            {{ $item->customer->name }} - {{ $item->customer->company }} - {{ $item->customer->business_type }} -
            {{ $item->customer->address }}
          </td>
          <td>{{ $item->service->name }}</td>
          <td>{{ $item->description }}</td>
          <td align="right">{{ format_number($item->amount) }}</td>
          <td>{{ $item->notes }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="9" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
  </table>
@endsection
