<section {{ $attributes->merge(['class' => 'usage-guide']) }} data-usage-guide aria-labelledby="usage-guide-title">
    <div class="usage-guide-summary">
        <span class="usage-guide-icon" aria-hidden="true">?</span>
        <div>
            <span class="eyebrow">Panduan</span>
            <h2 id="usage-guide-title">Cara ikut bermain</h2>
            <p>Mulai dalam tiga langkah.</p>
        </div>
        <button type="button" class="btn soft" data-usage-guide-open>Lihat panduan</button>
    </div>

    <dialog class="usage-guide-dialog" data-usage-guide-dialog aria-labelledby="usage-guide-dialog-title">
        <button type="button" class="pwa-dialog-close" aria-label="Tutup panduan" data-usage-guide-close><x-nav-icon name="close" /></button>
        <span class="eyebrow">Panduan</span>
        <h2 id="usage-guide-dialog-title">Mulai bermain dalam tiga langkah</h2>

        <ol class="usage-guide-steps">
            <li>
                <span>1</span>
                <div><strong>Buat atau masuk akun</strong><p>Akun diperlukan untuk mengelola pendaftaran.</p></div>
            </li>
            <li>
                <span>2</span>
                <div><strong>Pilih jadwal</strong><p>Buka sesi yang masih memiliki slot.</p></div>
            </li>
            <li>
                <span>3</span>
                <div><strong>Daftar dan hadir</strong><p>Pilih pembayaran lalu datang sesuai jadwal.</p></div>
            </li>
        </ol>

        <div class="usage-guide-actions">
            @guest
                <a class="btn primary" href="{{ route('register') }}">Buat akun</a>
                <a class="btn soft" href="{{ route('login') }}">Masuk</a>
            @else
                <a class="btn soft" href="{{ route('public-sessions.index') }}">Lihat jadwal</a>
            @endguest
            <button type="button" class="btn dark" data-usage-guide-close>Mengerti</button>
        </div>
    </dialog>
</section>
