@extends('report.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>

        <th>No</th>
        <th>Tanggal</th>
        <th>Jenis</th>
        <th>Rincian</th>
        <th>Sales</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $index => $item)
        <tr>

          <td>{{ $item['no'] }}</td>
          <td>{{ $item['date'] }}</td>
          <td>{{ $item['type'] }}</td>
          <td>{{ $item['detail'] }}</td>
          <td>{{ $item['sales'] }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="10" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
  </table>
@endsection
