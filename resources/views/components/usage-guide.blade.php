<section {{ $attributes->merge(['class' => 'usage-guide']) }} aria-labelledby="usage-guide-title">
    <div class="usage-guide-head">
        <div>
            <span class="eyebrow">PANDUAN SINGKAT</span>
            <h2 id="usage-guide-title">Cara menggunakan website</h2>
        </div>
        <div class="usage-guide-actions">
            @guest
                <a class="btn primary" href="{{ route('register') }}">Buat akun</a>
                <a class="btn soft" href="{{ route('login') }}">Masuk</a>
            @else
                <a class="btn soft" href="{{ route('public-sessions.index') }}">Lihat jadwal</a>
            @endguest
        </div>
    </div>

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
</section>
