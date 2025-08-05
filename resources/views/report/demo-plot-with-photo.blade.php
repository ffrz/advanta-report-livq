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
        <th>Foto Terkini</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $index => $item)
        @php
          $src = null;

          $tryPaths = [$item->latest_image_path ?? null, $item->image_path ?? null];

          foreach ($tryPaths as $path) {
              if ($path) {
                  $fullPath = public_path($path);
                  if (file_exists($fullPath)) {
                      $imageData = base64_encode(file_get_contents($fullPath));
                      $src = 'data:image/png;base64,' . $imageData;
                      break;
                  }
              }
          }
        @endphp
        <tr>
          <td align="right">{{ $index + 1 }}</td>
          <td style="width:15%">
            BS: {{ $item->user->name }}<br />
            Petani: {{ $item->owner_name }}<br />
            Lokasi: {{ $item->field_location }}
          </td>
          <td style="width:15%;white-space: nowrap">
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
