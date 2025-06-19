@extends('report.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Tanggal</th>
        <th>Jenis</th>
        <th>Klien</th>
        <th>Detail</th>
      </tr>
    </thead>
    <tbody>
      @php
        $totalClosing = 0;
        $totalInteraksi = 0;
        $totalClosingAmount = 0;
      @endphp

      @forelse ($items as $index => $item)
        @php
          if ($item['type'] == 'Closing') {
            $totalClosing++;
            $totalClosingAmount += $item['data_1'];
          } else {
            $totalInteraksi++;
          }
        @endphp
        <tr style="background:{{ $item['type'] == 'Closing' ? 'lightgreen' : 'white' }};">
          <td align="right">{{ $index + 1 }}</td>
          <td>{{ $item['date'] }}</td>
          <td>{{ $item['type'] }}</td>
          <td>{{ $item['client'] }}</td>
          <td>{{ $item['detail'] }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="6" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <div style="margin-top: 20px;text-align: center">
    <strong>Summary:</strong><br>
    Total Closing: {{ format_number($totalClosing) }}<br>
    Total Interaksi: {{ format_number($totalInteraksi) }}<br>
    Jumlah Closing: Rp. {{ format_number($totalClosingAmount) }}
  </div>
@endsection
