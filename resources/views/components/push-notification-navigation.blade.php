<nav class="notification-subnav" aria-label="Menu notifikasi">
    <a @class(['active' => request()->routeIs('push-notifications.index')]) href="{{ route('push-notifications.index') }}">Kirim & riwayat</a>
    <a @class(['active' => request()->routeIs('push-notifications.subscribers')]) href="{{ route('push-notifications.subscribers') }}">Member aktif</a>
</nav>
