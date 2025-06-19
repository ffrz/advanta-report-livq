@extends('report.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>ID</th>
        <th>Klien</th>
        <th>Status</th>
        <th>Sales</th>
        <th>Closing Terakhir</th>
        <th>Intraksi Terakhir</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $index => $item)
        <tr>
          <td align="right">{{ $index + 1 }}</td>
          <td>{{ $item['id'] }}</td>
          <td>{{ $item['client'] }}</td>          
          <td>{{ $item['status'] }}</td>
          <td>{{ $item['sales'] }}</td>
          <td>{{ $item['last_closing'] }}</td>
          <td>{{ $item['last_interaction'] }} - {{ $item['engagement_level'] }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="10" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
  </table>
@endsection
