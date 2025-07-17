@extends('export.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th>Bulan</th>
        <th>BS</th>
        <th>Kegiatan</th>
        <th>Varietas</th>
        <th>Lokasi</th>
        <th>Biaya</th>
        <th>Status</th>
        <th>Catatan</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($items as $row)
        <tr>
          <td>{{ \Carbon\Carbon::parse($row->date)->translatedFormat('F Y') }}</td>
          <td>{{ $row->bs_name }}</td>
          <td>{{ $row->activity_type }}</td>
          <td>{{ $row->product_name }}</td>
          <td>{{ $row->location }}</td>
          <td align="right">{{ number_format($row->cost, 0, ',', '.') }}</td>
          <td>{{ \App\Models\ActivityPlan::Statuses[$row->status] }}</td>
          <td>{{ $row->notes }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
@endsection
