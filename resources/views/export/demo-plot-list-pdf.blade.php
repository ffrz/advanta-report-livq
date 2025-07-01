@extends('export.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>BS</th>
        <th>Pemilik</th>
        <th>No HP</th>
        <th>Lokasi</th>
        <th>Tgl Tanam</th>
        <th>Umur Tanam</th>
        <th>Last Visit</th>
        <th>Status Tanaman</th>
        <th>Status Demplot</th>
        <th>Catatan</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $index => $item)
        <tr>
          <td align="right">{{ $index + 1 }}</td>
          <td>{{ optional($item->user)->name }}</td>
          <td>{{ $item->owner_name }}</td>
          <td>{{ $item->owner_phone }}</td>
          <td>{{ $item->field_location }}</td>
          <td>{{ \Carbon\Carbon::parse($item->plant_date)->translatedFormat('j F Y') }}</td>
          <td align="right">
            @if ($item->plant_date && $item->active)
              {{ (int) \Carbon\Carbon::parse($item->plant_date)->diffInDays(\Carbon\Carbon::now()) }} hari
            @else
              -
            @endif
          </td>
          <td>{{ $item->last_visit ? \Carbon\Carbon::parse($item->last_visit)->translatedFormat('j F Y') : '' }}</td>
          <td>{{ \App\Models\DemoPlot::PlantStatuses[$item->plant_status] }}</td>
          <td>{{ $item->active ? 'Aktif' : 'Tidak Aktif' }}</td>
          <td>{{ $item->notes }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="8" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
  </table>
@endsection
