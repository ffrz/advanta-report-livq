@extends('report.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th>Area</th>
        <th>Crops</th>
        <th>Checker</th>
        <th>Kiosk / Distributor</th>
        <th>Hybrid</th>
        <th>Check Date</th>
        <th>Lot Package</th>
        <th>Quantity (Kg)</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $index => $item)
        <tr>
          <td>{{ $item->area }}</td>
          <td>{{ $item->product->category->name }}</td>
          <td>{{ $item->user->name }}</td>
          <td>{{ $item->customer->name }}</td>
          <td>{{ $item->product->name }}</td>
          <td>{{ format_date($item->check_date) }}</td>
          <td>{{ $item->lot_package }}</td>
          <td align="right">{{ $item->quantity }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="10" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
  </table>
@endsection
