<section {{ $attributes->merge(['class' => 'usage-guide']) }} data-usage-guide aria-labelledby="usage-guide-title">
    <div class="usage-guide-summary">
        <span class="usage-guide-icon" aria-hidden="true">?</span>
        <div>
            <span class="eyebrow">PANDUAN SINGKAT</span>
            <h2 id="usage-guide-title">Cara menggunakan website</h2>
            <p>Tiga langkah singkat untuk mulai ikut bermain.</p>
        </div>
        <button type="button" class="btn soft" data-usage-guide-open>Lihat panduan</button>
    </div>

    <dialog class="usage-guide-dialog" data-usage-guide-dialog aria-labelledby="usage-guide-dialog-title">
        <button type="button" class="pwa-dialog-close" aria-label="Tutup panduan" data-usage-guide-close><x-nav-icon name="close" /></button>
        <span class="usage-guide-dialog-icon" aria-hidden="true">🏸</span>
        <span class="eyebrow">PANDUAN SINGKAT</span>
        <h2 id="usage-guide-dialog-title">Mulai bermain dalam tiga langkah</h2>

        <ol class="usage-guide-steps">
            <li>
                <span>1</span>
                <div><strong>Buat atau masuk akun</strong><p>Semua pemain perlu akun. Nomor WhatsApp boleh dikosongkan.</p></div>
            </li>
            <li>
                <span>2</span>
                <div><strong>Pilih jadwal bermain</strong><p>Buka detail jadwal, lalu pilih pembayaran transfer atau tunai.</p></div>
            </li>
            <li>
                <span>3</span>
                <div><strong>Masuk daftar dan hadir</strong><p>Pastikan nama sudah terdaftar, lalu datang sesuai lokasi dan waktu sesi.</p></div>
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
