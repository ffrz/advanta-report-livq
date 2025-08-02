@extends('export.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>BS</th>
        <th>Varietas</th>
        <th>Pemilik</th>
        <th>No HP</th>
        <th>Lokasi</th>
        <th>Umur Tanam</th>
        <th>Populasi</th>
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
          <td>{{ $item->product->name }}</td>
          <td>{{ $item->owner_name }}</td>
          <td>{{ $item->owner_phone }}</td>
          <td>{{ $item->field_location }}</td>
          <td align="right">
            @if ($item->plant_date && $item->active)
              {{ (int) \Carbon\Carbon::parse($item->plant_date)->diffInDays(\Carbon\Carbon::now()) }} hari
            @else
              -
            @endif
          </td>
          <td align="right">{{ format_number($item->population) }}</td>
          <td>{{ $item->last_visit ? \Carbon\Carbon::parse($item->last_visit)->translatedFormat('j F Y') : '' }}</td>
          <td>{{ \App\Models\DemoPlot::PlantStatuses[$item->plant_status] }}</td>
          <td>{{ $item->active ? 'Aktif' : 'Tidak Aktif' }}</td>
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
