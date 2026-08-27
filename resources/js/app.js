import Alpine from 'alpinejs';

window.Alpine = Alpine;

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

Alpine.data('deviceStats', (statusUrl) => ({
    loading: true,
    online: 0,
    offline: 0,

    async init() {
        try {
            const response = await fetch(statusUrl, {
                headers: { Accept: 'application/json' },
            });

            if (response.ok) {
                const statuses = Object.values(await response.json());
                this.online = statuses.filter(Boolean).length;
                this.offline = statuses.length - this.online;
            }
        } catch {
            // keep counters at zero on failure
        } finally {
            this.loading = false;
        }
    },
}));

Alpine.data('deviceBoard', (initialDevices = []) => ({
    allDevices: Array.isArray(initialDevices) ? initialDevices : [],
    searchQuery: '',
    selectedTag: '',
    selectedOs: '',
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',

    get availableTags() {
        const tagsSet = new Set();
        this.allDevices.forEach(d => {
            if (Array.isArray(d.tags)) {
                d.tags.forEach(t => {
                    if (t) tagsSet.add(t);
                });
            }
        });
        return Array.from(tagsSet).sort();
    },

    get filteredDevices() {
        return this.allDevices.filter(c => {
            if (this.selectedOs && c.os_type !== this.selectedOs) {
                return false;
            }
            if (this.selectedTag) {
                const tagMatch = (c.tags_relation && c.tags_relation.some(t => String(t.id) === String(this.selectedTag) || t.name === this.selectedTag))
                              || (c.tags && c.tags.some(t => String(t) === String(this.selectedTag)));
                if (!tagMatch) return false;
            }
            if (this.searchQuery && this.searchQuery.trim()) {
                const q = this.searchQuery.toLowerCase().trim();
                const matchName = c.name && c.name.toLowerCase().includes(q);
                const matchIp = c.ip_address && c.ip_address.toLowerCase().includes(q);
                const matchPort = String(c.vnc_port || '').includes(q);
                const matchLoc = c.location && c.location.toLowerCase().includes(q);
                const matchDesc = c.description && c.description.toLowerCase().includes(q);
                const matchTags = c.tags && c.tags.some(t => t.toLowerCase().includes(q));
                return matchName || matchIp || matchPort || matchLoc || matchDesc || matchTags;
            }
            return true;
        });
    },

    resetFilters() {
        this.searchQuery = '';
        this.selectedTag = '';
        this.selectedOs = '';
    },

    /** @type {Record<string, boolean|undefined>} */
    statuses: {},
    connecting: false,
    pendingId: null,
    connectingId: null,
    targetName: '',
    boardError: '',
    term: { open: false, title: '', lines: [], running: false },

    isButtonLoading(id) {
        return this.pendingId === id || (this.connecting && this.connectingId === id);
    },

    showBoardError(message) {
        this.boardError = message;
        setTimeout(() => {
            this.boardError = '';
        }, 6000);
    },

    statusClass(id) {
        const status = this.statuses[id];

        if (status === undefined) {
            return 'bg-gray-300';
        }

        return status ? 'bg-green-500' : 'bg-red-500';
    },

    statusLabel(id) {
        const status = this.statuses[id];

        if (status === undefined) {
            return '—';
        }

        return status ? 'Online' : 'Offline';
    },

    closeTerminal() {
        this.term.open = false;
    },

    scrollTerm() {
        requestAnimationFrame(() => {
            const body = this.$refs?.termBody;
            if (body) {
                body.scrollTop = body.scrollHeight;
            }
        });
    },

    async typeLine(text, cls = 'text-gray-300') {
        await sleep(180);
        this.term.lines.push({ text, cls });
        this.scrollTerm();
    },

    async ping(id, host, port, url) {
        this.term.open = true;
        this.term.title = `Diagnosa Koneksi — ${host}`;
        this.term.lines = [];
        this.term.running = true;

        await this.typeLine(`$ nodehub ping --host ${host}`, 'text-emerald-400 font-bold');

        let data = null;

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
            });

            if (response.ok) {
                data = await response.json();
            }
        } catch {
            data = null;
        }

        await sleep(120);

        if (!data) {
            await this.typeLine(`→ Gagal menghubungi server web portal`, 'text-red-400');
            this.term.running = false;
            return;
        }

        // ICMP Ping Result
        if (data.icmp_ok) {
            await this.typeLine(`→ ICMP System Ping [${host}] ... REPLIED (Network Card Reachable)`, 'text-emerald-400');
        } else {
            await this.typeLine(`→ ICMP System Ping [${host}] ... NO RESPONSE`, 'text-amber-400');
        }

        // VNC Port Result
        if (data.vnc_ok) {
            await this.typeLine(`→ VNC Service Port [${port}] ... CONNECTED (${data.vnc_latency ?? 0} ms)`, 'text-emerald-400');
            this.statuses = { ...this.statuses, [id]: true };
        } else {
            await this.typeLine(`→ VNC Service Port [${port}] ... CLOSED / NOT LISTENING`, 'text-amber-400');
        }

        // SSH Port Result
        if (data.ssh_ok) {
            await this.typeLine(`→ SSH Service Port [22] ... CONNECTED (${data.ssh_latency ?? 0} ms)`, 'text-emerald-400');
        } else {
            await this.typeLine(`→ SSH Service Port [22] ... CLOSED`, 'text-slate-400');
        }

        await this.typeLine('------------------------------------------------------------------', 'text-slate-600');

        if (data.vnc_ok) {
            await this.typeLine('✔ HASIL: Layanan VNC Siap — Perangkat dapat langsung diremote!', 'text-emerald-400 font-bold');
        } else if (data.icmp_ok || data.ssh_ok) {
            await this.typeLine('ℹ HASIL: Jaringan/SSH Aktif, namun Service VNC (port ' + port + ') belum berjalan di komputer target.', 'text-amber-400 font-bold');
        } else {
            this.statuses = { ...this.statuses, [id]: false };
            await this.typeLine('✘ HASIL: Perangkat sama sekali tidak dapat dijangkau di jaringan.', 'text-red-400 font-bold');
        }

        this.term.running = false;
        this.scrollTerm();
    },

    async connect(event, id) {
        const form = event.target;
        const targetId = id ?? (form.dataset.id ? parseInt(form.dataset.id, 10) : null);
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        this.targetName = form.dataset.name ?? '';
        this.boardError = '';
        this.pendingId = targetId;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf ?? '',
                },
            });

            const data = await response.json().catch(() => ({}));

            if (response.ok && data.redirect) {
                this.connectingId = targetId;
                this.connecting = true;
                await sleep(800);
                window.location.href = data.redirect;

                return;
            }

            this.showBoardError(data.message ?? 'Unable to start remote session.');
        } catch {
            this.showBoardError('Network error — please try again.');
        } finally {
            this.pendingId = null;
        }
    },
}));

// Dynamic Client-Side Inactivity Auto-Lock (from user preference meta tag)
const timeoutMeta = document.querySelector('meta[name="auto-lock-timeout"]')?.content;
const timeoutMinutes = parseInt(timeoutMeta || '20', 10);
const INACTIVITY_LIMIT_MS = (isNaN(timeoutMinutes) || timeoutMinutes <= 0 ? 20 : timeoutMinutes) * 60 * 1000;
let inactivityTimerId = null;

function handleInactivityLock() {
    if (window.location.pathname.endsWith('/lock') || window.location.pathname.endsWith('/login')) {
        return;
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrf) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/lock-session';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = '_token';
        input.value = csrf;
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    } else {
        window.location.href = '/lock';
    }
}

function resetInactivityTimer() {
    if (inactivityTimerId) clearTimeout(inactivityTimerId);
    inactivityTimerId = setTimeout(handleInactivityLock, INACTIVITY_LIMIT_MS);
}

if (!window.location.pathname.endsWith('/lock') && !window.location.pathname.endsWith('/login')) {
    ['mousemove', 'keydown', 'mousedown', 'touchstart', 'scroll', 'click'].forEach((evt) => {
        window.addEventListener(evt, resetInactivityTimer, { passive: true });
    });
    resetInactivityTimer();
}

Alpine.start();
