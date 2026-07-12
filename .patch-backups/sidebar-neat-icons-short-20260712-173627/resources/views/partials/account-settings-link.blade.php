@php
    $accountSettingsUrl = \Illuminate\Support\Facades\Route::has('account.settings')
        ? route('account.settings')
        : url('/akun/pengaturan');

    $accountSettingsActive = request()->routeIs('account.settings') || request()->is('akun/pengaturan');
@endphp

<a href="{{ $accountSettingsUrl }}"
   class="account-settings-sidebar-link {{ $accountSettingsActive ? 'active' : '' }}">
    <span class="account-settings-sidebar-icon">⚙</span>
    <span>Pengaturan Akun</span>
</a>
