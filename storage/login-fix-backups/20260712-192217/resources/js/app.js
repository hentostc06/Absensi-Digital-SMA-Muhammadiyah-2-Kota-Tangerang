import '../css/app.css';
import './legacy.js';

function bootAuthTabs() {
    document.querySelectorAll('[data-auth-tabs] button').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('[data-auth-tabs] button').forEach(item => item.classList.remove('active'));
            button.classList.add('active');

            const username = document.getElementById('login-username');
            const password = document.getElementById('login-password');

            if (username && password) {
                username.value = button.dataset.demoUser || '';
                password.value = button.dataset.demoPassword || '';
                username.focus();
            }
        });
    });

    document.querySelectorAll('[data-toggle-password]').forEach(button => {
        button.addEventListener('click', () => {
            const input = button.parentElement?.querySelector('input');
            if (!input) return;

            input.type = input.type === 'password' ? 'text' : 'password';
            button.textContent = input.type === 'password' ? 'lihat' : 'tutup';
        });
    });
}

function bootAccountForm() {
    const form = document.querySelector('[data-account-form]');
    if (!form) return;

    const sync = role => {
        document.querySelectorAll('[data-role-card]').forEach(card => {
            card.classList.toggle('active', card.dataset.roleCard === role);
        });

        document.querySelectorAll('[data-fields-for]').forEach(group => {
            group.classList.toggle('hidden', group.dataset.fieldsFor !== role);
        });
    };

    form.querySelectorAll('input[name="role"]').forEach(input => {
        input.addEventListener('change', () => sync(input.value));
    });

    const checked = form.querySelector('input[name="role"]:checked');
    sync(checked?.value || 'siswa');
}

document.addEventListener('DOMContentLoaded', () => {
    bootAuthTabs();
    bootAccountForm();
});
