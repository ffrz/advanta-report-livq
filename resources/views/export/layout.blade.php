<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title }}</title>
  <style>
    body {
      font-family: sans-serif;
      font-size: 10pt;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    th,
    td {
      border: 1px solid black;
      padding: 2px 5px;
    }

    th {
      background-color: #f2f2f2;
      text-align: center;
    }
  </style>
</head>

<body>
  @php
    $logoPath = public_path(\App\Models\Setting::value('company_logo_path') ?? '');
    $logoRelative = \App\Models\Setting::value('company_logo_path');
  @endphp
  @if ($logoRelative && file_exists($logoPath))
    <div class="header-logo" style="text-align: center;">
      <img src="{{ $logoRelative }}" alt="Logo Perusahaan" style="width: 24px; height: auto;  margin: 0 auto;" />
    </div>
  @endif
  <h4 style="margin:0;text-align:center;">{{ \App\Models\Setting::value('company_name') }}</h4>
  <h2 style="margin:0;text-align:center;">{{ $title }}</h2>
  <div style="text-align:center;">
    <small>Dibuat oleh <b>{{ Auth::user()->username }}</b>
      pada {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y H:i:s') }}
      - {{ env('APP_NAME') }} v{{ env('APP_VERSION_STR') }}</small>
  </div>
  @yield('content')
</body>

</html>
