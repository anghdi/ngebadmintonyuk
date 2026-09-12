@extends('layouts.app')
@section('title', 'Member')
@section('content')
<div class="page-head"><div><span class="eyebrow">Komunitas</span><h1>Daftar member</h1><p>Akun dan kuota member.</p></div><a class="btn dark" href="{{ route('play-sessions.index') }}">Buat sesi</a></div>

<div class="card table-card">
    <table>
        <thead><tr><th>Member</th><th>Bergabung</th><th>Paket</th><th>Sisa kuota</th><th></th></tr></thead>
        <tbody>
        @forelse($members as $member)
            <tr>
                <td><div class="identity-cell"><span>{{ $member->initials() }}</span><div><strong>{{ $member->name }}</strong><small>{{ $member->email }}{{ $member->phone ? ' · '.$member->phone : '' }}</small></div></div></td>
                <td>{{ $member->created_at->translatedFormat('d M Y') }}</td>
                <td>{{ $member->memberships->count() }}</td>
                <td><strong class="credit-count">{{ (int) $member->memberships->sum('balance') }}×</strong></td>
                <td><a class="link" href="{{ route('members.show', $member) }}">Detail</a></td>
            </tr>
        @empty
            <tr><td colspan="5"><div class="empty-state compact"><h2>Belum ada member</h2><p>Member baru akan muncul di sini.</p></div></td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $members->links() }}
</div>
@endsection
