@extends('report.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Sales</th>
        <th>Total Client Baru</th>
        <th>Total Interaksi</th>
        <th>Total Closing</th>
        <th>Jumlah Closing (Rp)</th>
      </tr>
    </thead>
    <tbody>
      @php
        $total_new_client = 0;
        $total_interaction = 0;
        $total_closing = 0;
        $total_closing_amount = 0;
      @endphp
      @forelse ($items as $index => $item)
        <tr>
          <td style="text-align:right">{{ $index + 1 }}</td>
          <td>{{ $item->name }}</td>
          <td style="text-align:right">{{ format_number($item->new_clients_count) }}</td>
          <td style="text-align:right">{{ format_number($item->interactions_count) }}</td>
          <td style="text-align:right">{{ format_number($item->closings_count) }}</td>
          <td style="text-align:right">{{ format_number($item->total_amount) }}</td>
        </tr>
        @php
          $total_new_client += $item->new_clients_count;
          $total_interaction += $item->interactions_count;
          $total_closing += $item->closings_count;
          $total_closing_amount += $item->total_amount;
        @endphp
      @empty
        <tr>
          <td colspan="6" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr>
        <th></th>
        <th style="text-align:right">Grand Total</th>
        <th style="text-align:right">{{ format_number($total_new_client) }}</th>
        <th style="text-align:right">{{ format_number($total_interaction) }}</th>
        <th style="text-align:right">{{ format_number($total_closing) }}</th>
        <th style="text-align:right">{{ format_number($total_closing_amount) }}</th>
      </tr>
    </tfoot>
  </table>
@endsection
