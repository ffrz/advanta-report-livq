@extends('report.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>ID</th>
        <th>Layanan</th>
        <th>Deskripsi</th>
        <th>Pelanggan</th>
        <th>Status</th>
        <th>Sales</th>
        <th>Tanggal Mulai</th>
        <th>Tanggal Berakhir</th>
        <th>Catatan</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $index => $item)
        <tr>
          <td align="right">{{ $index + 1 }}</td>
          <td>{{ $item->id }}</td>
          <td>{{ $item->service->name }}</td>
          <td>{{ $item->description }}</td>
          <td>{{ $item->customer->name }} - {{ $item->customer->company }}</td>
          <td>{{ \App\Models\CustomerService::Statuses[$item->status] }}</td>
          <td>{{ $item->closing ? $item->closing->user->name : '' }}</td>
          <td>{{ $item->start_date }}</td>
          <td>{{ $item->end_date }}</td>
          <td>{{ $item->notes }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="10" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
  </table>
@endsection
