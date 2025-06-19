@extends('report.layout', [
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
        <th>Customer</th>
        <th>Perusahaan</th>
        <th>Alamat</th>
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
          <td>{{ $item->sales_name }}</td>
          <td>{{ $item->customer_name }}</td>
          <td>{{ $item->company }}</td>
          <td>{{ $item->address }}</td>
          <td>{{ $item->service_name }}</td>
          <td>{{ $item->description }}</td>
          <td style="text-align: right">{{ format_number($item->amount) }}</td>
          <td>{{ $item->notes }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="11" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
  </table>
@endsection
