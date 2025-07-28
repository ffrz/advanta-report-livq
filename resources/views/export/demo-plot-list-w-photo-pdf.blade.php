@extends('export.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th style="width:1%">No</th>
        <th>Info</th>
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
          <td>
            <div>BS: {{ optional($item->user)->name }}</div>
            <div>Varietas: {{ $item->product->name }}</div>
            <div>Pemilik Lahan: {{ $item->owner_name }}</div>
            <div>No. HP: {{ $item->owner_phone }}</div>
            <div>Lokasi Lahan: {{ $item->field_location }}</div>
            <div>
              Umur Tanaman:
              @if ($item->plant_date && $item->active)
                {{ (int) \Carbon\Carbon::parse($item->plant_date)->diffInDays(\Carbon\Carbon::now()) }} hari
              @else
                -
              @endif
            </div>
            <div>Last Visit: {{ $item->last_visit ? \Carbon\Carbon::parse($item->last_visit)->translatedFormat('j F Y') : '' }}</div>
            <div>Status Tanaman: {{ \App\Models\DemoPlot::PlantStatuses[$item->plant_status] }}</div>
            <div>Status Demo Plot: {{ $item->active ? 'Aktif' : 'Tidak Aktif' }}</div>
            <div>Catatan: {{ $item->notes ?: '-' }}</div>
          </td>
          <td>
            @if ($src)
              <img src="{{ $src }}" alt="Demo Plot Photo" style="max-height: 300px; max-width:600px;" />
              <br>
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="11" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
  </table>
@endsection
