@extends('report.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Layanan</th>
        <th>Total Closing</th>
        <th>Jumlah Deal (Rp)</th>
        <th>Rata-rata (Rp)</th>
      </tr>
    </thead>
    <tbody>
      @php
        $total_closings = 0;
        $total_amount = 0;
      @endphp
      @forelse ($items as $index => $item)
        <tr>
          <td align="right">{{ $index + 1 }}</td>
          <td>{{ $item->service_name }}</td>
          <td align="right">{{ format_number($item->total_closings) }}</td>
          <td align="right">{{ format_number($item->total_amount) }}</td>
          <td align="right">{{ $item->total_closings ? format_number($item->total_amount / $item->total_closings) : 0 }}</td>
        </tr>
        @php
          $total_closings += $item->total_closings;
          $total_amount += $item->total_amount;
        @endphp
      @empty
        <tr>
          <td colspan="5" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr>
        <th colspan="2" style="text-align: right">Total</th>
        <th style="text-align: right">{{ format_number($total_closings) }}</th>
        <th style="text-align: right">{{ format_number($total_amount) }}</th>
        <th style="text-align: right">{{ format_number($total_closings > 0 ? $total_amount / $total_closings : '0' ) }}</th>
      </tr>
    </tfoot>
  </table>
@endsection
