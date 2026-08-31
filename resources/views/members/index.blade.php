@extends('layouts.app')
@section('title', 'Member')
@section('content')
<div class="page-head"><div><span class="eyebrow">KOMUNITAS</span><h1>Daftar member</h1><p>Akun baru langsung muncul di sini setelah mendaftar.</p></div><a class="btn dark" href="{{ route('play-sessions.index') }}">Buat sesi main</a></div>

<div class="card table-card">
    <table>
        <thead><tr><th>MEMBER</th><th>BERGABUNG</th><th>PAKET</th><th>SISA KUOTA</th><th></th></tr></thead>
        <tbody>
        @forelse($members as $member)
            <tr>
                <td><div class="identity-cell"><span>{{ $member->initials() }}</span><div><strong>{{ $member->name }}</strong><small>{{ $member->email }}{{ $member->phone ? ' · '.$member->phone : '' }}</small></div></div></td>
                <td>{{ $member->created_at->translatedFormat('d M Y') }}</td>
                <td>{{ $member->memberships->count() }}</td>
                <td><strong class="credit-count">{{ (int) $member->memberships->sum('balance') }}×</strong></td>
                <td><a class="link" href="{{ route('members.show', $member) }}">Kelola →</a></td>
            </tr>
        @empty
            <tr><td colspan="5"><div class="empty-state compact"><span>◎</span><h2>Belum ada member</h2><p>Bagikan halaman pendaftaran kepada teman-temanmu.</p></div></td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $members->links() }}
</div>
@endsection
