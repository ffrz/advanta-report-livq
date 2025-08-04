@extends('report.layout', [
    'title' => $title,
])

@section('content')
  <style>
    .report td {
      vertical-align: top !important;
    }
  </style>
  <table class="report">
    <thead>
      <tr>
        <th>No</th>
        @if (!$user)
          <th>BS</th>
        @endif
        <th>Varietas</th>
        <th>Petani</th>
        <th>Lokasi</th>
        <th>Populasi</th>
        <th>Umur</th>
        <th>Status Tanaman</th>
        <th>Catatan</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $index => $item)
        <tr>
          <td align="right">{{ $index + 1 }}</td>
          @if (!$user)
            <td style="white-space: nowrap">{{ $item->user->name }}</td>
          @endif
          <td style="white-space: nowrap">{{ $item->product->name }}</td>
          <td>{{ $item->owner_name }}</td>
          <td>{{ $item->field_location }}</td>
          <td align="right">{{ format_number($item->population) }}</td>
          <td align="right">
            {{ (int) \Carbon\Carbon::parse($item->plant_date)->diffInDays(\Carbon\Carbon::now()) }}
          </td>
          <td style="white-space: nowrap">{{ \App\Models\DemoPlot::PlantStatuses[$item->plant_status] }}</td>
          <td>{{ $item->notes }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="12" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
  </table>
@endsection
