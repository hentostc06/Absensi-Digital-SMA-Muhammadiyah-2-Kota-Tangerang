
@php
    $accountSettingsUrl = \Illuminate\Support\Facades\Route::has('account.settings')
        ? route('account.settings')
        : url('/akun/pengaturan');

    $accountSettingsActive = request()->routeIs('account.settings') || request()->is('akun/pengaturan');
@endphp

<a href="{{ $accountSettingsUrl }}"
   class="account-settings-sidebar-link {{ $accountSettingsActive ? 'active' : '' }}">
    <span class="account-settings-sidebar-icon">
        <svg class="bc-sidebar-svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 15.5A3.5 3.5 0 1 0 12 8.5a3.5 3.5 0 0 0 0 7Z"/>
            <path d="M19 13.5v-3l-2.1-.4a7.5 7.5 0 0 0-.8-1.9l1.2-1.8-2.1-2.1-1.8 1.2a7.5 7.5 0 0 0-1.9-.8L11.1 2h-3l-.4 2.1a7.5 7.5 0 0 0-1.9.8L4 3.7 1.9 5.8l1.2 1.8a7.5 7.5 0 0 0-.8 1.9L.2 9.9v3l2.1.4c.2.7.5 1.3.8 1.9L1.9 17l2.1 2.1 1.8-1.2c.6.3 1.2.6 1.9.8l.4 2.1h3l.4-2.1c.7-.2 1.3-.5 1.9-.8l1.8 1.2 2.1-2.1-1.2-1.8c.3-.6.6-1.2.8-1.9l2.1-.4Z"/>
        </svg>
    </span>
    <span>Pengaturan Akun</span>
</a>
