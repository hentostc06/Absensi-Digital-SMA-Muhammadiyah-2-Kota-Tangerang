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
