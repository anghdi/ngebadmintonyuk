<dialog
    class="pwa-install-dialog"
    data-pwa-app-gate
    data-pwa-known-installed="{{ auth()->user()->pwa_installed_at !== null ? 'true' : 'false' }}"
    data-pwa-installation-url="{{ route('app-installations.store') }}"
    aria-labelledby="pwa-app-gate-title"
>
    <img src="{{ asset('pwa-icon-192.png') }}" alt="">
    <span class="eyebrow">APLIKASI SUDAH TERPASANG</span>
    <h2 id="pwa-app-gate-title">Buka dari aplikasi</h2>
    <p>Gunakan NgeBadmintonYuk dari layar utama perangkat.</p>
    <small>Jika ikon sudah dihapus, pasang kembali melalui menu browser.</small>
</dialog>
