const csrf = () =>
    document.querySelector('meta[name="csrf-token"]')?.content;

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>'"]/g, character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#39;',
        '"': '&quot;'
    })[character]);
}

function attendanceStatusClass(status) {
    const allowed = ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'];
    return allowed.includes(String(status).toLowerCase())
        ? String(status).toLowerCase()
        : 'hadir';
}

window.dynamicQr = function ({ tokenUrl, attendanceUrl }) {
    const image = document.getElementById('qr-image');
    const loader = document.getElementById('qr-loader');
    const countdown = document.getElementById('countdown');
    const countdownRing = document.getElementById('countdown-ring');
    const serverTime = document.getElementById('server-time');
    const qrStatus = document.getElementById('qr-status');
    const attendanceCount = document.getElementById('attendance-count');
    const attendanceList = document.getElementById('attendance-list');
    const lastSync = document.getElementById('last-sync');

    let countdownTimer = null;
    let attendanceTimer = null;
    let loadingToken = false;

    async function fetchJson(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {})
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Terjadi kesalahan pada server.');
        }

        return data;
    }

    function startCountdown(seconds) {
        clearInterval(countdownTimer);

        let remaining = Number(seconds) || 30;
        const maximum = 30;

        const render = () => {
            countdown.textContent = Math.max(remaining, 0);

            const progress = Math.max(0, Math.min(1, remaining / maximum));
            countdownRing?.style.setProperty('--progress', `${progress * 360}deg`);

            if (remaining <= 0) {
                clearInterval(countdownTimer);
                loadToken();
                return;
            }

            remaining -= 1;
        };

        render();
        countdownTimer = setInterval(render, 1000);
    }

    async function loadToken() {
        if (loadingToken) {
            return;
        }

        loadingToken = true;

        if (loader) {
            loader.classList.remove('hidden');
        }

        try {
            const data = await fetchJson(tokenUrl);

            image.onload = () => {
                loader?.classList.add('hidden');
                image.classList.add('ready');
            };

            image.src = data.svg;
            serverTime.textContent = new Date(data.server_time).toLocaleString(
                'id-ID',
                {
                    dateStyle: 'full',
                    timeStyle: 'medium'
                }
            );

            qrStatus.textContent = 'Aktif';
            qrStatus.className = 'qr-status online';

            startCountdown(data.expires_in);
        } catch (error) {
            qrStatus.textContent = 'Gagal memuat';
            qrStatus.className = 'qr-status offline';

            if (loader) {
                loader.innerHTML = `
                    <span class="loader-error">!</span>
                    <p>${escapeHtml(error.message)}</p>
                `;
            }
        } finally {
            loadingToken = false;
        }
    }

    function renderAttendance(items) {
        if (!items.length) {
            attendanceList.innerHTML = `
                <tr class="empty-attendance-row">
                    <td colspan="4">
                        <div class="attendance-empty-state">
                            <span>⌁</span>
                            <strong>Belum ada siswa yang melakukan scan</strong>
                            <p>Data akan muncul otomatis setelah siswa berhasil memindai QR.</p>
                        </div>
                    </td>
                </tr>
            `;

            return;
        }

        attendanceList.innerHTML = items.map(item => {
            const status = attendanceStatusClass(item.status);
            const initial = escapeHtml(String(item.name || '?').charAt(0).toUpperCase());

            return `
                <tr class="attendance-row-new">
                    <td>
                        <span class="nis-cell">${escapeHtml(item.nis)}</span>
                    </td>

                    <td>
                        <div class="student-cell">
                            <span class="student-avatar">${initial}</span>
                            <strong>${escapeHtml(item.name)}</strong>
                        </div>
                    </td>

                    <td>
                        <span class="attendance-badge ${status}">
                            ${escapeHtml(item.status)}
                        </span>
                    </td>

                    <td>${escapeHtml(item.time)}</td>
                </tr>
            `;
        }).join('');
    }

    async function loadAttendance() {
        try {
            const data = await fetchJson(attendanceUrl);

            attendanceCount.textContent = data.count;
            renderAttendance(data.items || []);

            if (lastSync) {
                lastSync.textContent =
                    `Diperbarui ${new Date().toLocaleTimeString('id-ID')}`;
            }
        } catch (error) {
            if (lastSync) {
                lastSync.textContent = 'Sinkronisasi gagal';
            }
        }
    }

    loadToken();
    loadAttendance();

    attendanceTimer = setInterval(loadAttendance, 3000);

    window.addEventListener('beforeunload', () => {
        clearInterval(countdownTimer);
        clearInterval(attendanceTimer);
    });
};

window.studentScanner = function (url) {
    let locked = false;
    const feedbackBox = document.getElementById('scan-feedback');

    function showFeedback(success, message, detail = '') {
        feedbackBox.className =
            `scan-feedback ${success ? 'success' : 'error'}`;

        feedbackBox.innerHTML = `
            <div class="icon">${success ? '✓' : '!'}</div>
            <strong>${escapeHtml(message)}</strong>
            <span>${escapeHtml(detail)}</span>
        `;

        setTimeout(() => {
            feedbackBox.className = 'scan-feedback';
        }, 2500);
    }

    async function submitToken(token) {
        if (locked || !token) {
            return;
        }

        locked = true;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    Accept: 'application/json'
                },
                body: JSON.stringify({ token })
            });

            const data = await response.json();

            showFeedback(
                response.ok,
                data.message,
                data.subject
                    ? `${data.subject} • ${data.time} • ${data.status}`
                    : ''
            );
        } catch (error) {
            showFeedback(false, 'Koneksi ke server gagal.');
        }

        setTimeout(() => {
            locked = false;
        }, 1800);
    }

    document
        .getElementById('manual-form')
        ?.addEventListener('submit', event => {
            event.preventDefault();

            submitToken(
                document.getElementById('manual-token').value.trim()
            );
        });

    const startCamera = () => {
        if (typeof Html5Qrcode === 'undefined') {
            setTimeout(startCamera, 300);
            return;
        }

        const scanner = new Html5Qrcode('reader');

        scanner.start(
            { facingMode: 'environment' },
            {
                fps: 10,
                qrbox: {
                    width: 250,
                    height: 250
                }
            },
            decodedText => submitToken(decodedText),
            () => {}
        ).catch(() => {
            showFeedback(
                false,
                'Kamera tidak dapat dibuka.',
                'Periksa izin kamera atau gunakan input manual.'
            );
        });
    };

    startCamera();
};
