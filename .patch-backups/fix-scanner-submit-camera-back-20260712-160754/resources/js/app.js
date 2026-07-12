import '../css/app.css';
import { Html5Qrcode } from 'html5-qrcode';

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
const secureCameraContext = () => window.isSecureContext || ['localhost', '127.0.0.1', '::1'].includes(location.hostname);
const esc = v => String(v ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));

function bootPasswordToggle(){
    document.querySelectorAll('[data-toggle-password]').forEach(b => b.addEventListener('click', () => {
        const i = b.parentElement?.querySelector('input');
        if(!i) return;
        i.type = i.type === 'password' ? 'text' : 'password';
        b.textContent = i.type === 'password' ? 'Lihat' : 'Tutup';
    }));
}

function bootAccountForm(){
    const f = document.querySelector('[data-account-form]');
    if(!f) return;
    const sync = r => {
        document.querySelectorAll('[data-role-card]').forEach(c => c.classList.toggle('active', c.dataset.roleCard === r));
        document.querySelectorAll('[data-fields-for]').forEach(g => g.classList.toggle('hidden', g.dataset.fieldsFor !== r));
    };
    f.querySelectorAll('input[name="role"]').forEach(i => i.addEventListener('change', () => sync(i.value)));
    sync(f.querySelector('input[name="role"]:checked')?.value || 'siswa');
}

const statusClass = s => ['hadir','terlambat','izin','sakit','alpa'].includes(String(s || '').toLowerCase()) ? String(s).toLowerCase() : 'hadir';

window.dynamicQr = function({tokenUrl, attendanceUrl}){
    const image = document.getElementById('qr-image');
    const loader = document.getElementById('qr-loader');
    const countdown = document.getElementById('countdown');
    const ring = document.getElementById('countdown-ring');
    const serverTime = document.getElementById('server-time');
    const qrStatus = document.getElementById('qr-status');
    const attendanceCount = document.getElementById('attendance-count');
    const attendanceList = document.getElementById('attendance-list');
    const lastSync = document.getElementById('last-sync');
    let ct = null, at = null, loading = false;

    async function get(url, opt = {}){
        const r = await fetch(url, {
            ...opt,
            headers: {
                Accept:'application/json',
                'X-Requested-With':'XMLHttpRequest',
                ...(opt.headers || {})
            }
        });
        const d = await r.json().catch(() => ({}));
        if(!r.ok) throw new Error(d.message || 'Terjadi kesalahan server.');
        return d;
    }

    function tick(sec){
        clearInterval(ct);
        let left = Number(sec) || 30;
        const max = 30;
        const render = () => {
            if(countdown) countdown.textContent = Math.max(left, 0);
            ring?.style.setProperty('--progress', `${Math.max(0, Math.min(1, left / max)) * 360}deg`);
            if(left <= 0){
                clearInterval(ct);
                loadToken();
                return;
            }
            left--;
        };
        render();
        ct = setInterval(render, 1000);
    }

    async function loadToken(){
        if(loading) return;
        loading = true;
        loader?.classList.remove('hidden');
        try{
            const d = await get(tokenUrl);
            if(image){
                image.onload = () => {
                    loader?.classList.add('hidden');
                    image.classList.add('ready');
                };
                image.src = d.svg;
            }
            if(serverTime) serverTime.textContent = new Date(d.server_time).toLocaleString('id-ID', {dateStyle:'full', timeStyle:'medium'});
            if(qrStatus){
                qrStatus.textContent = 'Aktif';
                qrStatus.className = 'qr-status online';
            }
            tick(d.expires_in);
        }catch(e){
            if(qrStatus){
                qrStatus.textContent = 'Tidak aktif';
                qrStatus.className = 'qr-status offline';
            }
            if(loader) loader.innerHTML = `<span class="loader-error">!</span><p>${esc(e.message)}</p>`;
        }finally{
            loading = false;
        }
    }

    function render(items){
        if(!attendanceList) return;
        if(!items.length){
            attendanceList.innerHTML = '<tr><td colspan="4"><div class="attendance-empty-state"><strong>Belum ada siswa yang melakukan scan</strong><p>Data akan muncul otomatis setelah siswa berhasil memindai QR.</p></div></td></tr>';
            return;
        }
        attendanceList.innerHTML = items.map(i => `
            <tr>
                <td><span class="nis-cell">${esc(i.nis)}</span></td>
                <td><div class="student-cell"><span class="student-avatar">${esc(String(i.name || '?')[0].toUpperCase())}</span><strong>${esc(i.name)}</strong></div></td>
                <td><span class="attendance-badge ${statusClass(i.status)}">${esc(i.status)}</span></td>
                <td>${esc(i.time)}</td>
            </tr>
        `).join('');
    }

    async function loadAttendance(){
        try{
            const d = await get(attendanceUrl);
            if(attendanceCount) attendanceCount.textContent = d.count;
            render(d.items || []);
            if(lastSync) lastSync.textContent = `Diperbarui ${new Date().toLocaleTimeString('id-ID')}`;
        }catch(e){
            if(lastSync) lastSync.textContent = 'Sinkronisasi gagal';
        }
    }

    loadToken();
    loadAttendance();
    at = setInterval(loadAttendance, 3000);
    addEventListener('beforeunload', () => {
        clearInterval(ct);
        clearInterval(at);
    });
};

window.studentScanner = function(url){
    const readerId = 'reader';
    const feedback = document.getElementById('scan-feedback');
    const startBtn = document.getElementById('camera-start');
    const stopBtn = document.getElementById('camera-stop');
    const select = document.getElementById('camera-device');
    const manualForm = document.getElementById('manual-form');
    const manualToken = document.getElementById('manual-token');
    let scanner = null, locked = false, lastToken = '';

    function show(ok, msg, detail = ''){
        if(!feedback) return;
        feedback.className = `scan-feedback ${ok ? 'success' : 'error'}`;
        feedback.innerHTML = `<div class="icon">${ok ? '✓' : '!'}</div><strong>${esc(msg)}</strong><span>${esc(detail)}</span>`;
    }

    async function submit(token){
        token = String(token || '').trim();
        if(locked || !token || token === lastToken) return;
        locked = true;
        lastToken = token;

        try{
            const r = await fetch(url, {
                method:'POST',
                headers:{
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN':csrf(),
                    Accept:'application/json'
                },
                body:JSON.stringify({token})
            });
            const d = await r.json().catch(() => ({}));
            show(r.ok, d.message || (r.ok ? 'Absensi berhasil dicatat.' : 'Absensi gagal.'), d.subject ? `${d.subject} • ${d.time} • ${d.status}` : '');
            if(!r.ok) setTimeout(() => lastToken = '', 1200);
        }catch(e){
            show(false, 'Koneksi ke server gagal.', 'Periksa jaringan lalu coba lagi.');
            setTimeout(() => lastToken = '', 1200);
        }finally{
            setTimeout(() => locked = false, 1400);
        }
    }

    async function devices(){
        const cams = await Html5Qrcode.getCameras();
        if(select){
            select.innerHTML = cams.map((c, i) => `<option value="${esc(c.id)}">${esc(c.label || `Kamera ${i + 1}`)}</option>`).join('');
        }
        return cams;
    }

    async function start(){
        if(!secureCameraContext()){
            show(false, 'Kamera wajib HTTPS atau localhost.', 'Akses sistem melalui domain HTTPS. HTTP dari IP LAN akan ditolak browser.');
            return;
        }

        try{
            if(startBtn) startBtn.disabled = true;
            const cams = await devices();
            if(!cams.length) throw new Error('Perangkat kamera tidak ditemukan.');
            scanner = scanner || new Html5Qrcode(readerId, {verbose:false});
            const id = select?.value || cams.find(c => /back|rear|environment/i.test(c.label))?.id || cams[0].id;

            await scanner.start(
                {deviceId:{exact:id}},
                {fps:10, qrbox:{width:260, height:260}, aspectRatio:1.0},
                txt => submit(txt),
                () => {}
            );

            if(stopBtn) stopBtn.disabled = false;
            show(true, 'Kamera aktif.', 'Arahkan kamera ke QR Code yang ditampilkan guru.');
        }catch(e){
            if(startBtn) startBtn.disabled = false;
            const m = String(e?.message || e || 'Kamera tidak dapat dibuka.');
            show(false, 'Kamera tidak dapat dibuka.', /Permission|NotAllowed|denied/i.test(m) ? 'Klik izinkan kamera pada browser, lalu tekan Coba Lagi.' : m);
        }
    }

    async function stop(){
        try{
            if(scanner?.isScanning) await scanner.stop();
            show(true, 'Kamera dihentikan.', 'Tekan Aktifkan Kamera untuk memindai lagi.');
        }catch(e){
            show(false, 'Gagal menghentikan kamera.');
        }finally{
            if(startBtn) startBtn.disabled = false;
            if(stopBtn) stopBtn.disabled = true;
        }
    }

    manualForm?.addEventListener('submit', e => {
        e.preventDefault();
        submit(manualToken?.value);
    });
    startBtn?.addEventListener('click', start);
    stopBtn?.addEventListener('click', stop);

    if(!secureCameraContext()){
        show(false, 'Kamera belum bisa dipakai.', 'Gunakan HTTPS/domain production atau localhost untuk testing.');
    }else{
        show(true, 'Siap memindai.', 'Tekan Aktifkan Kamera untuk meminta izin kamera.');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    bootPasswordToggle();
    bootAccountForm();
});

/* === CUSTOM CONFIRM MODAL PATCH === */
(function () {
    function html(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function normalizeMessage(message) {
        message = String(message || '').trim();

        const map = {
            'Hapus guru ini?': {
                title: 'Hapus Data Guru?',
                message: 'Data guru akan dihapus jika belum memiliki jadwal atau riwayat. Jika sudah memiliki riwayat, sistem akan menonaktifkan akun agar laporan tetap aman.',
                action: 'Ya, proses'
            },
            'Hapus siswa ini?': {
                title: 'Hapus Data Siswa?',
                message: 'Data siswa akan dihapus jika belum memiliki riwayat absensi. Jika sudah memiliki riwayat, sistem akan menonaktifkan akun agar laporan tetap aman.',
                action: 'Ya, proses'
            },
            'Hapus akun ini?': {
                title: 'Hapus Akun?',
                message: 'Akun akan diproses dengan aman. Jika akun sudah memiliki riwayat penting, sistem akan menonaktifkan akun, bukan merusak data laporan.',
                action: 'Ya, proses'
            },
            'Reset password akun ini?': {
                title: 'Reset Password?',
                message: 'Password akun akan dibuat ulang. Berikan password baru hanya kepada pemilik akun yang bersangkutan.',
                action: 'Ya, reset'
            },
            'Nonaktifkan akun ini?': {
                title: 'Nonaktifkan Akun?',
                message: 'Akun tidak bisa login sampai admin mengaktifkannya kembali.',
                action: 'Ya, nonaktifkan'
            }
        };

        if (map[message]) {
            return map[message];
        }

        let lower = message.toLowerCase();
        let title = 'Konfirmasi Tindakan';
        let action = 'Ya, lanjutkan';

        if (lower.includes('hapus') || lower.includes('delete')) {
            title = 'Hapus Data?';
            action = 'Ya, hapus';
        } else if (lower.includes('reset')) {
            title = 'Reset Data?';
            action = 'Ya, reset';
        } else if (lower.includes('nonaktif')) {
            title = 'Nonaktifkan Data?';
            action = 'Ya, nonaktifkan';
        } else if (lower.includes('tutup')) {
            title = 'Tutup Sesi?';
            action = 'Ya, tutup';
        }

        return {
            title: title,
            message: message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
            action: action
        };
    }

    function ensureModal() {
        let modal = document.getElementById('bc-confirm-modal');

        if (modal) {
            return modal;
        }

        modal = document.createElement('div');
        modal.id = 'bc-confirm-modal';
        modal.className = 'bc-confirm hidden';
        modal.innerHTML = `
            <div class="bc-confirm-backdrop" data-confirm-cancel></div>
            <div class="bc-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="bc-confirm-title">
                <div class="bc-confirm-icon">!</div>
                <div class="bc-confirm-content">
                    <span class="bc-confirm-kicker">Konfirmasi Admin</span>
                    <h2 id="bc-confirm-title">Konfirmasi Tindakan</h2>
                    <p id="bc-confirm-message">Apakah Anda yakin?</p>
                </div>
                <div class="bc-confirm-actions">
                    <button type="button" class="bc-confirm-cancel" data-confirm-cancel>Batal</button>
                    <button type="button" class="bc-confirm-ok" data-confirm-ok>Ya, lanjutkan</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        return modal;
    }

    window.badcodingConfirm = function (message) {
        const modal = ensureModal();
        const data = normalizeMessage(message);

        modal.querySelector('#bc-confirm-title').innerHTML = html(data.title);
        modal.querySelector('#bc-confirm-message').innerHTML = html(data.message);
        modal.querySelector('[data-confirm-ok]').innerHTML = html(data.action);

        modal.classList.remove('hidden');
        document.body.classList.add('bc-confirm-open');

        return new Promise(function (resolve) {
            let done = false;

            function close(result) {
                if (done) return;
                done = true;

                modal.classList.add('hidden');
                document.body.classList.remove('bc-confirm-open');

                modal.querySelectorAll('[data-confirm-cancel]').forEach(function (button) {
                    button.removeEventListener('click', cancel);
                });

                modal.querySelector('[data-confirm-ok]').removeEventListener('click', ok);
                document.removeEventListener('keydown', esc);

                resolve(result);
            }

            function cancel() {
                close(false);
            }

            function ok() {
                close(true);
            }

            function esc(event) {
                if (event.key === 'Escape') {
                    close(false);
                }
            }

            modal.querySelectorAll('[data-confirm-cancel]').forEach(function (button) {
                button.addEventListener('click', cancel);
            });

            modal.querySelector('[data-confirm-ok]').addEventListener('click', ok);
            document.addEventListener('keydown', esc);
        });
    };

    function convertInlineConfirms() {
        document.querySelectorAll('[onsubmit*="confirm"]').forEach(function (element) {
            const raw = element.getAttribute('onsubmit') || '';
            const match = raw.match(/confirm\((['"`])([\s\S]*?)\1\)/);

            if (match) {
                element.dataset.confirm = match[2];
            }

            element.removeAttribute('onsubmit');
        });

        document.querySelectorAll('[onclick*="confirm"]').forEach(function (element) {
            const raw = element.getAttribute('onclick') || '';
            const match = raw.match(/confirm\((['"`])([\s\S]*?)\1\)/);

            if (match) {
                element.dataset.confirm = match[2];
            }

            element.removeAttribute('onclick');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        convertInlineConfirms();

        document.addEventListener('submit', async function (event) {
            const form = event.target.closest('form[data-confirm]');

            if (!form || form.dataset.confirmed === '1') {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            const ok = await window.badcodingConfirm(form.dataset.confirm);

            if (ok) {
                form.dataset.confirmed = '1';
                form.requestSubmit ? form.requestSubmit() : form.submit();
            }
        }, true);

        document.addEventListener('click', async function (event) {
            const target = event.target.closest('[data-confirm]');

            if (!target || target.tagName === 'FORM') {
                return;
            }

            if (target.dataset.confirmed === '1') {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            const ok = await window.badcodingConfirm(target.dataset.confirm);

            if (!ok) {
                return;
            }

            target.dataset.confirmed = '1';

            if (target.tagName === 'A' && target.href) {
                window.location.href = target.href;
                return;
            }

            target.click();
        }, true);
    });
})();

/* === MOBILE CAMERA COOKIE PREFLIGHT PATCH === */
(function () {
    function setScanFeedback(type, title, message) {
        const el = document.getElementById('scan-feedback');
        if (!el) return;

        el.className = `scan-feedback ${type === 'ok' ? 'success' : 'error'}`;
        el.innerHTML = `
            <div class="icon">${type === 'ok' ? '✓' : '!'}</div>
            <strong>${title}</strong>
            <span>${message}</span>
        `;
    }

    function cookieWritable() {
        if (!navigator.cookieEnabled) return false;

        try {
            document.cookie = "bc_cookie_test=1; path=/; SameSite=Lax";
            return document.cookie.includes("bc_cookie_test=1");
        } catch (e) {
            return false;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const cameraButton = document.getElementById('camera-start');

        if (!cameraButton) return;

        if (!window.isSecureContext) {
            setScanFeedback(
                'error',
                'Kamera wajib HTTPS.',
                'Buka dari https://absensi.badcoding.biz.id, bukan http://IP-LAN atau http://localhost di HP.'
            );
        } else if (!cookieWritable()) {
            setScanFeedback(
                'error',
                'Cookie browser diblokir.',
                'Aktifkan cookie di browser HP agar login dan izin kamera berjalan normal.'
            );
        } else {
            setScanFeedback(
                'ok',
                'Browser siap.',
                'Cookie aktif. Tekan Aktifkan Kamera lalu pilih Izinkan.'
            );
        }

        cameraButton.addEventListener('click', function (event) {
            if (!window.isSecureContext) {
                event.preventDefault();
                event.stopImmediatePropagation();
                setScanFeedback(
                    'error',
                    'Kamera ditolak browser.',
                    'Kamera HP hanya aktif lewat HTTPS. Gunakan https://absensi.badcoding.biz.id.'
                );
                return;
            }

            if (!cookieWritable()) {
                event.preventDefault();
                event.stopImmediatePropagation();
                setScanFeedback(
                    'error',
                    'Cookie browser belum aktif.',
                    'Buka pengaturan browser HP, izinkan cookies untuk situs ini, lalu refresh halaman.'
                );
            }
        }, true);
    });
})();

/* === SERVER QR AND BACK CAMERA FINAL FIX === */
(function () {
    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function isSecureCameraContext() {
        return window.isSecureContext || ['localhost', '127.0.0.1', '::1'].includes(location.hostname);
    }

    async function readJson(url) {
        const response = await fetch(url, {
            method: 'GET',
            cache: 'no-store',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const contentType = response.headers.get('content-type') || '';
        const text = await response.text();

        if (!contentType.includes('application/json')) {
            throw new Error('Server tidak mengembalikan JSON. Cek login/session atau HTTPS.');
        }

        const data = JSON.parse(text);

        if (!response.ok) {
            throw new Error(data.message || 'Request gagal.');
        }

        return data;
    }

    window.dynamicQr = function ({ tokenUrl, attendanceUrl }) {
        const image = document.getElementById('qr-image');
        const loader = document.getElementById('qr-loader');
        const countdown = document.getElementById('countdown');
        const ring = document.getElementById('countdown-ring');
        const serverTime = document.getElementById('server-time');
        const qrStatus = document.getElementById('qr-status');
        const attendanceCount = document.getElementById('attendance-count');
        const attendanceList = document.getElementById('attendance-list');
        const lastSync = document.getElementById('last-sync');

        let countdownTimer = null;
        let attendanceTimer = null;
        let loading = false;

        function setLoader(message, error = false) {
            if (!loader) return;
            loader.classList.remove('hidden');
            loader.innerHTML = `<span class="${error ? 'loader-error' : ''}">${escapeHtml(message)}</span>`;
        }

        function hideLoader() {
            loader?.classList.add('hidden');
        }

        function startCountdown(seconds) {
            clearInterval(countdownTimer);

            let left = Number(seconds) || 30;
            const max = 30;

            function render() {
                const safeLeft = Math.max(left, 0);

                if (countdown) countdown.textContent = safeLeft;
                ring?.style.setProperty('--progress', `${Math.max(0, Math.min(1, safeLeft / max)) * 360}deg`);

                if (left <= 0) {
                    clearInterval(countdownTimer);
                    loadToken();
                    return;
                }

                left--;
            }

            render();
            countdownTimer = setInterval(render, 1000);
        }

        async function loadToken() {
            if (loading) return;
            loading = true;

            image?.classList.remove('ready');
            setLoader('Memuat QR Code...');

            try {
                const data = await readJson(tokenUrl + (tokenUrl.includes('?') ? '&' : '?') + '_=' + Date.now());

                if (!data.svg) {
                    throw new Error('Data QR kosong dari server.');
                }

                if (image) {
                    image.onload = function () {
                        image.classList.add('ready');
                        hideLoader();
                    };

                    image.onerror = function () {
                        setLoader('QR gagal dirender. Refresh halaman.', true);
                    };

                    image.src = data.svg;
                }

                if (serverTime && data.server_time) {
                    serverTime.textContent = new Date(data.server_time).toLocaleString('id-ID', {
                        weekday: 'long',
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                }

                if (qrStatus) {
                    qrStatus.textContent = 'Aktif';
                    qrStatus.className = 'qr-status online';
                }

                startCountdown(data.expires_in || 30);
            } catch (error) {
                console.error(error);

                if (qrStatus) {
                    qrStatus.textContent = 'Error';
                    qrStatus.className = 'qr-status offline';
                }

                setLoader(error.message || 'QR gagal dimuat.', true);
            } finally {
                loading = false;
            }
        }

        function statusClass(status) {
            status = String(status || '').toLowerCase();
            return ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'].includes(status) ? status : 'hadir';
        }

        function renderAttendance(items) {
            if (!attendanceList) return;

            if (!items.length) {
                attendanceList.innerHTML = `
                    <tr>
                        <td colspan="4">
                            <div class="attendance-empty-state">
                                <strong>Belum ada siswa yang melakukan scan</strong>
                                <p>Data akan muncul otomatis setelah siswa berhasil memindai QR.</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            attendanceList.innerHTML = items.map(function (item) {
                const initial = String(item.name || '?').charAt(0).toUpperCase();

                return `
                    <tr>
                        <td><span class="nis-cell">${escapeHtml(item.nis)}</span></td>
                        <td>
                            <div class="student-cell">
                                <span class="student-avatar">${escapeHtml(initial)}</span>
                                <strong>${escapeHtml(item.name)}</strong>
                            </div>
                        </td>
                        <td><span class="attendance-badge ${statusClass(item.status)}">${escapeHtml(item.status)}</span></td>
                        <td>${escapeHtml(item.time)}</td>
                    </tr>
                `;
            }).join('');
        }

        async function loadAttendance() {
            try {
                const data = await readJson(attendanceUrl + (attendanceUrl.includes('?') ? '&' : '?') + '_=' + Date.now());

                if (attendanceCount) attendanceCount.textContent = data.count || 0;
                renderAttendance(data.items || []);
                if (lastSync) lastSync.textContent = 'Diperbarui ' + new Date().toLocaleTimeString('id-ID');
            } catch (error) {
                console.error(error);
                if (lastSync) lastSync.textContent = 'Sinkronisasi gagal';
            }
        }

        loadToken();
        loadAttendance();

        attendanceTimer = setInterval(loadAttendance, 3000);

        window.addEventListener('beforeunload', function () {
            clearInterval(countdownTimer);
            clearInterval(attendanceTimer);
        });
    };

    window.studentScanner = function (url) {
        const readerId = 'reader';
        const feedback = document.getElementById('scan-feedback');
        const startBtn = document.getElementById('camera-start');
        const stopBtn = document.getElementById('camera-stop');
        const select = document.getElementById('camera-device');
        const manualForm = document.getElementById('manual-form');
        const manualToken = document.getElementById('manual-token');

        let scanner = null;
        let locked = false;
        let lastToken = '';
        let currentCameraId = '';

        function show(ok, title, detail = '') {
            if (!feedback) return;

            feedback.className = `scan-feedback ${ok ? 'success' : 'error'}`;
            feedback.innerHTML = `
                <div class="icon">${ok ? '✓' : '!'}</div>
                <strong>${escapeHtml(title)}</strong>
                <span>${escapeHtml(detail)}</span>
            `;
        }

        async function submit(token) {
            token = String(token || '').trim();

            if (locked || !token || token === lastToken) return;

            locked = true;
            lastToken = token;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ token })
                });

                const data = await response.json().catch(() => ({}));

                show(
                    response.ok,
                    data.message || (response.ok ? 'Absensi berhasil dicatat.' : 'Absensi gagal.'),
                    data.subject ? `${data.subject} • ${data.time} • ${data.status}` : ''
                );

                if (!response.ok) {
                    setTimeout(() => lastToken = '', 1300);
                }
            } catch (error) {
                show(false, 'Koneksi ke server gagal.', 'Periksa jaringan lalu coba lagi.');
                setTimeout(() => lastToken = '', 1300);
            } finally {
                setTimeout(() => locked = false, 1500);
            }
        }

        async function warmPermission() {
            if (!navigator.mediaDevices?.getUserMedia) return;

            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    audio: false,
                    video: {
                        facingMode: { ideal: 'environment' }
                    }
                });

                stream.getTracks().forEach(track => track.stop());
            } catch (error) {
                // Nanti Html5Qrcode akan kasih pesan permission yang lebih jelas.
            }
        }

        async function getCameras() {
            await warmPermission();

            let cameras = await Html5Qrcode.getCameras();

            if (select) {
                select.innerHTML = cameras.map(function (camera, index) {
                    return `<option value="${escapeHtml(camera.id)}">${escapeHtml(camera.label || `Kamera ${index + 1}`)}</option>`;
                }).join('');
            }

            return cameras;
        }

        function findBackCamera(cameras) {
            const backRegex = /(back|rear|environment|belakang|kamera belakang|0,\s*facing back)/i;

            return cameras.find(camera => backRegex.test(camera.label || ''))
                || cameras[cameras.length - 1]
                || cameras[0];
        }

        async function stop() {
            try {
                if (scanner?.isScanning) {
                    await scanner.stop();
                }

                show(true, 'Kamera dihentikan.', 'Tekan Aktifkan Kamera untuk memindai lagi.');
            } catch (error) {
                show(false, 'Gagal menghentikan kamera.');
            } finally {
                if (startBtn) startBtn.disabled = false;
                if (stopBtn) stopBtn.disabled = true;
            }
        }

        async function startWithDevice(deviceId) {
            scanner = scanner || new Html5Qrcode(readerId, { verbose: false });

            await scanner.start(
                { deviceId: { exact: deviceId } },
                {
                    fps: 10,
                    qrbox: { width: 260, height: 260 },
                    aspectRatio: 1.0,
                    disableFlip: false
                },
                text => submit(text),
                () => {}
            );
        }

        async function startWithFacingMode() {
            scanner = scanner || new Html5Qrcode(readerId, { verbose: false });

            await scanner.start(
                { facingMode: { exact: 'environment' } },
                {
                    fps: 10,
                    qrbox: { width: 260, height: 260 },
                    aspectRatio: 1.0,
                    disableFlip: false
                },
                text => submit(text),
                () => {}
            );
        }

        async function start() {
            if (!isSecureCameraContext()) {
                show(false, 'Kamera wajib HTTPS.', 'Buka web dari https://absensi.badcoding.biz.id, bukan HTTP/IP LAN.');
                return;
            }

            try {
                if (startBtn) startBtn.disabled = true;

                const cameras = await getCameras();

                if (!cameras.length) {
                    throw new Error('Perangkat kamera tidak ditemukan.');
                }

                const preferred = select?.value
                    ? cameras.find(camera => camera.id === select.value)
                    : findBackCamera(cameras);

                currentCameraId = preferred?.id || '';

                if (select && currentCameraId) {
                    select.value = currentCameraId;
                }

                try {
                    await startWithDevice(currentCameraId);
                } catch (deviceError) {
                    console.warn('DeviceId gagal, coba facingMode environment.', deviceError);
                    await startWithFacingMode();
                }

                if (stopBtn) stopBtn.disabled = false;

                show(true, 'Kamera belakang aktif.', 'Arahkan kamera ke QR Code yang ditampilkan guru.');
            } catch (error) {
                console.error(error);

                if (startBtn) startBtn.disabled = false;

                const message = String(error?.message || error || '');

                if (/Permission|NotAllowed|denied|izin/i.test(message)) {
                    show(false, 'Izin kamera ditolak.', 'Buka pengaturan browser HP, izinkan kamera untuk situs ini, lalu refresh.');
                } else if (/Requested device not found|Overconstrained/i.test(message)) {
                    show(false, 'Kamera belakang tidak tersedia.', 'Pilih kamera lain dari dropdown lalu coba lagi.');
                } else {
                    show(false, 'Kamera tidak dapat dibuka.', message || 'Coba refresh halaman dan izinkan kamera.');
                }
            }
        }

        startBtn?.addEventListener('click', start);
        stopBtn?.addEventListener('click', stop);

        select?.addEventListener('change', async function () {
            currentCameraId = select.value;

            if (scanner?.isScanning) {
                await stop();
                await start();
            }
        });

        manualForm?.addEventListener('submit', function (event) {
            event.preventDefault();
            submit(manualToken?.value);
        });

        if (!isSecureCameraContext()) {
            show(false, 'Kamera belum bisa dipakai.', 'Gunakan HTTPS untuk membuka kamera HP.');
        } else {
            show(true, 'Siap memindai.', 'Tekan Aktifkan Kamera. Sistem akan memilih kamera belakang otomatis.');
        }
    };
})();
