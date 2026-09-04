@extends('layouts.app')

@section('title', 'Member Aktif Notifikasi')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">NOTIFIKASI</span>
            <h1>Member aktif</h1>
            <p>Daftar member yang telah mengizinkan notifikasi pada perangkatnya.</p>
        </div>
        <span class="notification-device-count"><b>{{ $memberCount }}</b> member · {{ $subscriptionCount }} perangkat</span>
    </div>

    <x-push-notification-navigation />

    <section class="card notification-subscriber-card">
        <div class="card-head">
            <div><span class="eyebrow">PENERIMA AKTIF</span><h2>Perangkat terdaftar</h2></div>
        </div>

        @forelse($members as $member)
            <article class="notification-subscriber-row">
                <span class="user-avatar notification-subscriber-avatar">{{ $member->initials() }}</span>
                <div class="notification-subscriber-profile">
                    <strong>{{ $member->name }}</strong>
                    <small>{{ $member->email }}{{ $member->phone ? ' · '.$member->phone : '' }}</small>
                    <div class="notification-subscriber-devices">
                        @foreach($member->pushSubscriptions as $subscription)
                            <span>
                                {{ $subscription->driver === 'webpush' ? 'Web Push' : 'FCM lama' }}
                                · {{ str($subscription->user_agent ?: 'Perangkat tidak dikenal')->limit(55) }}
                            </span>
                        @endforeach
                    </div>
                </div>
                <span class="notification-result">
                    <b>{{ $member->push_subscriptions_count }}</b>
                    <small>perangkat</small>
                </span>
            </article>
        @empty
            <div class="empty-state compact"><span>◉</span><h2>Belum ada member aktif</h2><p>Member akan muncul setelah mengaktifkan notifikasi.</p></div>
        @endforelse

        {{ $members->links() }}
    </section>
@endsection
