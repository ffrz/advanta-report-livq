@extends('report.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>ID</th>
        <th>Tanggal Daftar</th>
        <th>Klien</th>
        <th>Sales</th>
        <th>Status</th>
        <th>Catatan</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $index => $item)
        <tr>
          <td align="right">{{ $index + 1 }}</td>
          <td>{{ $item->id }}</td>
          <td>{{ $item->created_datetime }}</td>
          <td>
            {{ $item->name }} - {{ $item->company }} - {{ $item->business_type }}
            <br />{{ $item->phone }}
            @if ($item->address)
            <br /> {{ $item->address }}
            @endif
          </td>          
          <td>{{ $item->assigned_user_id ? $item->assigned_user->name : '-' }}</td>
          <td>{{ $item->active ? 'Aktif' : 'Tidak Aktif' }}</td>
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
