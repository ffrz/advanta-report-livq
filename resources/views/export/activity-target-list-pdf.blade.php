@extends('export.layout', ['title' => $title])

@section('content')
  <style>
    .no-break {
      page-break-inside: avoid;
    }
  </style>
  <table>
    <thead>
      <tr>
        <th rowspan="2">No</th>
        <th rowspan="2">Periode</th>
        <th rowspan="2">BS</th>
        <th colspan="4">Progress (%)</th>
        <th rowspan="2">Kegiatan</th>
        <th colspan="3">Kwartal</th>
        <th colspan="3">Bulan 1</th>
        <th colspan="3">Bulan 2</th>
        <th colspan="3">Bulan 3</th>
      </tr>
      <tr>
        <th style="width:1px">Q</th>
        <th style="width:1px">B1</th>
        <th style="width:1px">B2</th>
        <th style="width:1px">B3</th>

        <th style="width:1px">T</th>
        <th style="width:1px">P</th>
        <th style="width:1px">R</th>

        <th style="width:1px">T</th>
        <th style="width:1px">P</th>
        <th style="width:1px">R</th>

        <th style="width:1px">T</th>
        <th style="width:1px">P</th>
        <th style="width:1px">R</th>

        <th style="width:1px">T</th>
        <th style="width:1px">P</th>
        <th style="width:1px">R</th>
      </tr>
    </thead>
    <tbody class="no-break">
      @php
        $prevPeriod = null;

        $makeEmptyActivityRow = function () {
            return [
                'quarter_target_qty' => 0,
                'quarter_plan_qty' => 0,
                'quarter_real_qty' => 0,
                'month1_target_qty' => 0,
                'month1_plan_qty' => 0,
                'month1_real_qty' => 0,
                'month2_target_qty' => 0,
                'month2_plan_qty' => 0,
                'month2_real_qty' => 0,
                'month3_target_qty' => 0,
                'month3_plan_qty' => 0,
                'month3_real_qty' => 0,
            ];
        };

        $subtotalByActivity = [];
      @endphp

      @forelse ($items as $index => $item)
        @php
          $currentPeriod = $item['year'] . '-Q' . $item['quarter'];
          $rowspan = $types->count();
        @endphp

        {{-- Jika berganti periode, render subtotal terlebih dahulu --}}
        @if ($prevPeriod !== null && $currentPeriod !== $prevPeriod)
          @foreach ($subtotalByActivity as $activityName => $subtotal)
            <tr style="font-weight: bold; background-color: #f0f0f0">
              <td colspan="6"></td>
              <td>{{ $activityName }}</td>
              <td align="right">{{ $subtotal['quarter_target_qty'] }}</td>
              <td align="right">{{ $subtotal['quarter_plan_qty'] }}</td>
              <td align="right">{{ $subtotal['quarter_real_qty'] }}</td>

              <td align="right">{{ $subtotal['month1_target_qty'] }}</td>
              <td align="right">{{ $subtotal['month1_plan_qty'] }}</td>
              <td align="right">{{ $subtotal['month1_real_qty'] }}</td>

              <td align="right">{{ $subtotal['month2_target_qty'] }}</td>
              <td align="right">{{ $subtotal['month2_plan_qty'] }}</td>
              <td align="right">{{ $subtotal['month2_real_qty'] }}</td>

              <td align="right">{{ $subtotal['month3_target_qty'] }}</td>
              <td align="right">{{ $subtotal['month3_plan_qty'] }}</td>
              <td align="right">{{ $subtotal['month3_real_qty'] }}</td>
            </tr>
          @endforeach
          @php $subtotalByActivity = []; @endphp
        @endif

        @php $prevPeriod = $currentPeriod; @endphp

        @foreach ($types as $typeIndex => $type)
          <tr>
            @if ($typeIndex === 0)
              <td rowspan="{{ $rowspan }}" align="right">{{ $index + 1 }}</td>
              <td rowspan="{{ $rowspan }}">{{ $item['year'] }}-Q{{ $item['quarter'] }}</td>
              <td rowspan="{{ $rowspan }}">{{ $item['user']['name'] }}</td>
              <td rowspan="{{ $rowspan }}" align="right">{{ format_number($item['total_quarter_progress']) }}</td>
              <td rowspan="{{ $rowspan }}" align="right">{{ format_number($item['total_month1_progress']) }}</td>
              <td rowspan="{{ $rowspan }}" align="right">{{ format_number($item['total_month2_progress']) }}</td>
              <td rowspan="{{ $rowspan }}" align="right">{{ format_number($item['total_month3_progress']) }}</td>
            @endif

            <td>{{ $type->name }}</td>

            @php
              $t = $item->targets[$type->id] ?? ['quarter_qty' => 0, 'month1_qty' => 0, 'month2_qty' => 0, 'month3_qty' => 0];
              $p = $item->plans[$type->id] ?? ['quarter_qty' => 0, 'month1_qty' => 0, 'month2_qty' => 0, 'month3_qty' => 0];
              $r = $item->activities[$type->id] ?? ['quarter_qty' => 0, 'month1_qty' => 0, 'month2_qty' => 0, 'month3_qty' => 0];
            @endphp

            <td align="right">{{ $t['quarter_qty'] }}</td>
            <td align="right">{{ $p['quarter_qty'] }}</td>
            <td align="right">{{ $r['quarter_qty'] }}</td>

            <td align="right">{{ $t['month1_qty'] }}</td>
            <td align="right">{{ $p['month1_qty'] }}</td>
            <td align="right">{{ $r['month1_qty'] }}</td>

            <td align="right">{{ $t['month2_qty'] }}</td>
            <td align="right">{{ $p['month2_qty'] }}</td>
            <td align="right">{{ $r['month2_qty'] }}</td>

            <td align="right">{{ $t['month3_qty'] }}</td>
            <td align="right">{{ $p['month3_qty'] }}</td>
            <td align="right">{{ $r['month3_qty'] }}</td>
          </tr>

          @php
            $activityName = $type->name;

            if (!isset($subtotalByActivity[$activityName])) {
                $subtotalByActivity[$activityName] = $makeEmptyActivityRow();
            }

            $subtotalByActivity[$activityName]['quarter_target_qty'] += $t['quarter_qty'];
            $subtotalByActivity[$activityName]['quarter_plan_qty'] += $p['quarter_qty'];
            $subtotalByActivity[$activityName]['quarter_real_qty'] += $r['quarter_qty'];

            $subtotalByActivity[$activityName]['month1_target_qty'] += $t['month1_qty'];
            $subtotalByActivity[$activityName]['month1_plan_qty'] += $p['month1_qty'];
            $subtotalByActivity[$activityName]['month1_real_qty'] += $r['month1_qty'];

            $subtotalByActivity[$activityName]['month2_target_qty'] += $t['month2_qty'];
            $subtotalByActivity[$activityName]['month2_plan_qty'] += $p['month2_qty'];
            $subtotalByActivity[$activityName]['month2_real_qty'] += $r['month2_qty'];

            $subtotalByActivity[$activityName]['month3_target_qty'] += $t['month3_qty'];
            $subtotalByActivity[$activityName]['month3_plan_qty'] += $p['month3_qty'];
            $subtotalByActivity[$activityName]['month3_real_qty'] += $r['month3_qty'];
          @endphp
        @endforeach
      @empty
        <tr>
          <td colspan="20" align="center">Tidak ada data</td>
        </tr>
      @endforelse

      @if (!empty($subtotalByActivity))
        <tr>
          <td colspan="20"></td>
        </tr>

        @php
          $rowspan = count($subtotalByActivity);
          $isFirst = true;
        @endphp

        @foreach ($subtotalByActivity as $activityName => $subtotal)
          <tr style="font-weight: bold; background-color: #f0f0f0">
            @if ($isFirst)
              <td colspan="7" rowspan="{{ $rowspan }}">Subtotal {{ $item['year'] }}-Q{{ $item['quarter'] }}</td>
              @php $isFirst = false; @endphp
            @endif

            <td>{{ $activityName }}</td>
            <td align="right">{{ $subtotal['quarter_target_qty'] }}</td>
            <td align="right">{{ $subtotal['quarter_plan_qty'] }}</td>
            <td align="right">{{ $subtotal['quarter_real_qty'] }}</td>

            <td align="right">{{ $subtotal['month1_target_qty'] }}</td>
            <td align="right">{{ $subtotal['month1_plan_qty'] }}</td>
            <td align="right">{{ $subtotal['month1_real_qty'] }}</td>

            <td align="right">{{ $subtotal['month2_target_qty'] }}</td>
            <td align="right">{{ $subtotal['month2_plan_qty'] }}</td>
            <td align="right">{{ $subtotal['month2_real_qty'] }}</td>

            <td align="right">{{ $subtotal['month3_target_qty'] }}</td>
            <td align="right">{{ $subtotal['month3_plan_qty'] }}</td>
            <td align="right">{{ $subtotal['month3_real_qty'] }}</td>
          </tr>
        @endforeach

        <tr>
          <td colspan="20"></td>
        </tr>
      @endif

    </tbody>
  </table>
@endsection
