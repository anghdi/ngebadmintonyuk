@if(auth()->check() && ! auth()->user()->hasCompleteProfile() && ! request()->routeIs('profile.*'))
    <dialog class="profile-required-dialog" data-profile-required open aria-labelledby="profile-required-title" aria-describedby="profile-required-description">
        <div class="profile-required-card">
            <span class="profile-required-icon"><x-nav-icon name="users" /></span>
            <h2 id="profile-required-title">Lengkapi profil dulu</h2>
            <p id="profile-required-description">Isi nama, tanggal lahir, dan level bermain sebelum ikut sesi.</p>
            <a class="btn primary full" href="{{ route('profile.edit') }}" autofocus data-no-loading>Lengkapi profil</a>
            <form method="post" action="{{ route('logout') }}">@csrf<button type="submit" class="profile-logout" data-no-loading>Keluar akun</button></form>
        </div>
    </dialog>
@endif
