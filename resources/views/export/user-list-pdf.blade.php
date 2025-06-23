@extends('export.layout', [
    'title' => $title,
])

@section('content')
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Username</th>
        <th>Nama Lengkap</th>
        <th>Role</th>
        <th>Atasan</th>
        <th>Area Kerja</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $index => $item)
        <tr>
          <td align="right">{{ $index + 1 }}</td>
          <td>{{ $item->username }}</td>
          <td>{{ $item->name }}</td>
          <td>{{ \App\Models\User::Roles[$item->role] }}</td>
          <td>{{ $item->parent ? $item->parent->name : '' }}</td>
          <td>{{ $item->work_area }}</td>
          <td>{{ $item->active ? 'Aktif' : 'Non Aktif' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="7" align="center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
  </table>
@endsection
