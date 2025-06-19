@extends('report.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Tanggal</th>
        <th>Sales</th>
        <th>Total Interaksi</th>
        <th>Total Closing</th>
        <th>Total Client Baru</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $index => $item)
        <tr>
          <td align="right">{{ $index + 1 }}</td>
          <td>{{ $item->date }}</td>
          <td>{{ $item->sales_name }}</td>
          <td>{{ $item->total_interactions }}</td>
          <td>{{ $item->total_closings }}</td>
          <td>{{ $item->total_new_customers }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="6" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
  </table>
@endsection
