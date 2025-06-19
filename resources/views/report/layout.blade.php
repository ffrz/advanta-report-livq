@php
  if (!isset($subtitles)) {
      $subtitles = [];
  }
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title }}</title>
  <style>
    @media screen {
      .a4-landscape {
        width: 1122px;
        margin: 0 auto;
        padding: 40px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        background-color: white;
        overflow: hidden;
        box-sizing: border-box;
      }

      body {
        background: #ccc;
        display: flex;
        justify-content: center;
        padding: 50px;
      }
    }

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
  <div class="a4-landscape">
    <h4 style="margin:0;text-align:center;">{{ \App\Models\Setting::value('company_name', 'My Company') }}</h4>
    <h2 style="margin:0;text-align:center;">{{ $title }}</h2>
    @foreach ($subtitles as $subtitle)
      <h3 style="margin:0;text-align:center;">{{ $subtitle }}</h2>
    @endforeach
    <div style="text-align:center;font-size:10px;font-weight:normal;">
      Dibuat oleh <b>{{ Auth::user()->username }}</b>
      pada {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y H:i:s') }}
      - {{ env('APP_NAME') }} v{{ env('APP_VERSION_STR') }}
    </div>
    @yield('content')
  </div>
</body>

</html>
