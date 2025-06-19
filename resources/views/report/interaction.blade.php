

@extends('report.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>ID</th>
        <th>Tanggal</th>
        <th>Jenis</th>
        @if (!$user)
          <th>Sales</th>
        @endif
        <th>Client</th>
        <th>Layanan</th>
        <th>Engagement</th>
        <th>Subjek</th>
        <th>Summary</th>
        <th>Catatan</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $index => $item)
        <tr>
          <td align="right">{{ $index + 1 }}</td>
          <td>{{ $item->id }}</td>
          <td>{{ $item->date }}</td>
          <td>{{ \App\Models\Interaction::Types[$item->type] }}</td>
          @if (!$user)
            <td>{{ $item->user->name }} ({{ $item->user->username }})</td>
          @endif
          <td>
            {{ $item->customer->name }}
            @if ($item->customer->company)
              - {{ $item->customer->company }}
            @endif
            @if ($item->customer->business_type)
              - {{ $item->customer->business_type }}
            @endif
            @if ($item->customer->address)
              <br>{{ $item->customer->address }}
            @endif
          </td>
          <td>{{ $item->service->name }}</td>
          <td>{{ \App\Models\Interaction::EngagementLevels[$item->engagement_level] }}</td>
          <td>{{ $item->subject }}</td>
          <td>{{ $item->summary }}</td>
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
