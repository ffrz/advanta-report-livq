@extends('export.layout', ['title' => $title])

@section('content')
  <style>
    .no-break {
      page-break-inside: avoid;
    }
  </style>
  <table border="1" cellpadding="5" cellspacing="0">
    <thead>
      <tr>
        <th>BS</th>
        <th>KEGIATAN</th>
        <th>TOTAL TARGET</th>
        <th>Bulan 1</th>
        <th>Bulan 2</th>
        <th>Bulan 3</th>
        <th>Persentase (Q)</th>
      </tr>
    </thead>
    <tbody>
      @php
        $currentUserId = null;
      @endphp

      @foreach ($items as $item)
        @if ($currentUserId !== $item['user']['id'])
          @php
            $currentUserId = $item['user']['id'];
            $userName = $item['user']['name'];
            $weightedTotal = 0;
          @endphp

          {{-- Baris Nama BS --}}
          <tr>
            <td rowspan="{{ $types->count() + 1 }}">{{ $userName }}</td>
          </tr>

          @foreach ($types as $type)
            @php
              $target = $item['targets'][$type->id]['quarter_qty'] ?? 0;
              $r1 = $item['activities'][$type->id]['month1_qty'] ?? 0;
              $r2 = $item['activities'][$type->id]['month2_qty'] ?? 0;
              $r3 = $item['activities'][$type->id]['month3_qty'] ?? 0;

              $totalReal = $r1 + $r2 + $r3;

              // Progres kegiatan dalam persentase
              $activityProgress = $target > 0 ? $totalReal / $target : 0;

              // Dikalikan bobot kegiatan
              $weightedProgress = $activityProgress * $type->weight;

              // Total akumulasi
              $weightedTotal += $weightedProgress;
            @endphp

            <tr>
              <td>{{ $type->name }}</td>
              <td align="right">{{ $target }}</td>
              <td align="right">{{ $r1 }}</td>
              <td align="right">{{ $r2 }}</td>
              <td align="right">{{ $r3 }}</td>
              <td align="right">{{ format_number($weightedProgress, 2) }}</td>
            </tr>
          @endforeach

          {{-- Baris Total Progres --}}
          <tr style="font-weight: bold; background-color: #f0f0f0">
            <td colspan="6" align="right">Total</td>
            <td align="right">{{ format_number($weightedTotal, 2) }}</td>
          </tr>
        @endif
      @endforeach

      {{-- Total keseluruhan (opsional placeholder) --}}
      <tr style="font-weight: bold; background-color: #d0d0d0">
        <td colspan="6" align="right">Total Keseluruhan</td>
        <td align="right">—</td>
      </tr>
    </tbody>
  </table>
@endsection
