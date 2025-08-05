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
        <th style="width:1%">No</th>
        <th colspan="2">Info</th>
        <th style="width:20%">Catatan</th>
        <th>Foto</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $index => $item)
        @php
          $src = null;
          if ($item->image_path) {
              $imagePath = public_path($item->image_path);
              if ($item->image_path && file_exists($imagePath)) {
                  $imageData = base64_encode(file_get_contents($imagePath));
                  $src = 'data:image/png;base64,' . $imageData;
              }
          }

        @endphp
        <tr>
          <td align="right">{{ $index + 1 }}</td>
          <td style="width:20%">
            BS: {{ $item->user->name }}<br />
            Petani: {{ $item->owner_name }}<br />
            Lokasi: {{ $item->field_location }}
          </td>
          <td style="width:20%;white-space: nowrap">
            Populasi: {{ format_number($item->population) }}<br />
            Umur: {{ (int) \Carbon\Carbon::parse($item->plant_date)->diffInDays(\Carbon\Carbon::now()) }}<br />
            Kondisi: {{ \App\Models\DemoPlot::PlantStatuses[$item->plant_status] }}
          </td>
          <td>{{ $item->notes }}</td>
          <td>
            @if ($src)
              <img src="{{ $src }}" alt="Demo Plot Photo" style="max-height: 150px; max-width:200px;" />
              <br>
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="5" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
  </table>
@endsection
