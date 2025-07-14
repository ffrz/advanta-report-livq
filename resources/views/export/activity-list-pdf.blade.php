@extends('export.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Tanggal</th>
        <th>Jenis</th>
        <th>BS</th>
        <th>Lokasi</th>
        <th>Biaya (Rp)</th>
        <th>Status</th>
        <th>Catatan</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $index => $item)
        <tr>
          <td align="right">{{ $index + 1 }}</td>
          <td>{{ \Carbon\Carbon::parse($item->date)->format('d F Y') }}</td>
          <td>{{ $item->type->name }}</td>
          <td>{{ $item->user->name }}</td>
          <td>{{ $item->location }}</td>
          <td align="right">{{ format_number($item->cost) }}</td>
          <td>{{ \App\Models\Activity::Statuses[$item->status] }}</td>
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
