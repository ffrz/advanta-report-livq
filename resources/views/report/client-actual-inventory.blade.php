@extends('report.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th>Area</th>
        <th>Crops</th>
        <th>Cehcker</th>
        <th>Kiosk / Distributor</th>
        <th>Hybrid</th>
        <th>Lot Package</th>
        <th>Quantity (Kg)</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $index => $item)
        <tr>
          @if ($item->lastInventoryLog)
            <td>{{ $item->lastInventoryLog->area }}</td>
            <td>{{ $item->lastInventoryLog->product->category->name }}</td>
            <td>{{ $item->lastInventoryLog->user->name }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->lastInventoryLog->product->name }}</td>
            <td>{{ $item->lastInventoryLog->lot_package }}</td>
            <td align="right">{{ $item->lastInventoryLog->quantity }}</td>
          @else
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>{{ $item->name }}</td>
            <td>-</td>
            <td>-</td>
            <td align="right">-</td>
          @endif
        </tr>
      @empty
        <tr>
          <td colspan="10" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
  </table>
@endsection
