@php
    $accountSettingsActive = request()->routeIs('account.settings*') || request()->is('akun/pengaturan*');
@endphp

<a href="{{ route('account.settings') }}"
   class="sidebar-link sidebar-account-settings-link {{ $accountSettingsActive ? 'active' : '' }}">
    <span class="nav-icon" aria-hidden="true"></span>
    <span>Pengaturan Akun</span>
</a>
