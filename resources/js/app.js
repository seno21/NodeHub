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
                this.online = statuses.filter(s => (typeof s === 'object' ? s.vnc : Boolean(s))).length;
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
    currentPage: 1,
    perPage: 10,
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',

    init() {
        this.$watch('searchQuery', () => { this.currentPage = 1; });
        this.$watch('selectedTag', () => { this.currentPage = 1; });
        this.$watch('selectedOs', () => { this.currentPage = 1; });
        this.$watch('perPage', () => { this.currentPage = 1; });
    },

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
                return matchName || matchIp || matchPort || matchLoc || matchDesc;
            }
            return true;
        });
    },

    get totalPages() {
        return Math.ceil(this.filteredDevices.length / this.perPage) || 1;
    },

    get paginatedDevices() {
        const total = this.totalPages;
        if (this.currentPage > total) {
            this.currentPage = total;
        }
        const start = (this.currentPage - 1) * this.perPage;
        return this.filteredDevices.slice(start, start + this.perPage);
    },

    get showingStart() {
        if (this.filteredDevices.length === 0) return 0;
        return (this.currentPage - 1) * this.perPage + 1;
    },

    get showingEnd() {
        return Math.min(this.currentPage * this.perPage, this.filteredDevices.length);
    },

    get paginationPages() {
        const total = this.totalPages;
        const current = this.currentPage;
        const pages = [];
        
        let start = Math.max(1, current - 2);
        let end = Math.min(total, current + 2);

        if (current <= 3) {
            end = Math.min(total, 5);
        }
        if (current >= total - 2) {
            start = Math.max(1, total - 4);
        }

        for (let i = start; i <= end; i++) {
            pages.push(i);
        }
        return pages;
    },

    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
        }
    },

    prevPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
        }
    },

    goToPage(page) {
        const p = parseInt(page, 10);
        if (p >= 1 && p <= this.totalPages) {
            this.currentPage = p;
        }
    },

    resetFilters() {
        this.searchQuery = '';
        this.selectedTag = '';
        this.selectedOs = '';
        this.currentPage = 1;
    },

    /** @type {Record<string, boolean|undefined>} */
    statuses: {},
    connecting: false,
    pendingId: null,
    connectingId: null,
    targetName: '',
    boardError: '',
    term: { open: false, title: '', lines: [], running: false },
    checkingAll: false,
    batchSummary: null,

    detailModalOpen: false,
    selectedDevice: null,

    async checkAllConnections(statusUrl = '/computers/status', openTerminal = false) {
        if (this.checkingAll) return;
        this.checkingAll = true;

        if (openTerminal) {
            this.term.open = true;
            this.term.title = `Diagnosa Batch — Cek Semua Perangkat (${this.allDevices.length})`;
            this.term.lines = [];
            this.term.running = true;
            await this.typeLine(`$ nodehub ping --all --count=${this.allDevices.length}`, 'text-emerald-400 font-bold');
            await this.typeLine(`→ Memulai pemindaian koneksi ke ${this.allDevices.length} perangkat...`, 'text-cyan-400');
        }

        let data = null;
        try {
            const response = await fetch(statusUrl, {
                headers: { Accept: 'application/json' },
            });
            if (response.ok) {
                data = await response.json();
            }
        } catch {
            data = null;
        }

        if (!data) {
            this.showBoardError('Gagal melakukan pengecekan status koneksi massal.');
            if (openTerminal) {
                await this.typeLine(`→ ERROR: Gagal menghubungi server portal`, 'text-red-400');
                this.term.running = false;
            }
            this.checkingAll = false;
            return;
        }

        this.statuses = { ...this.statuses, ...data };

        let onlineCount = 0;
        let offlineCount = 0;

        for (const device of this.allDevices) {
            const st = data[device.id];
            const isVncOk = typeof st === 'object' ? Boolean(st.vnc) : Boolean(st);
            const isSshOk = typeof st === 'object' ? Boolean(st.ssh) : false;

            if (isVncOk) {
                onlineCount++;
                if (openTerminal) {
                    await this.typeLine(`✔ [ONLINE]  ${device.name} (${device.ip_address}:${device.vnc_port})`, 'text-emerald-400');
                }
            } else if (isSshOk) {
                offlineCount++;
                if (openTerminal) {
                    await this.typeLine(`⚠ [PORT OFF] ${device.name} (${device.ip_address}:${device.vnc_port}) — SSH Ok, VNC Off`, 'text-amber-400');
                }
            } else {
                offlineCount++;
                if (openTerminal) {
                    await this.typeLine(`✘ [OFFLINE] ${device.name} (${device.ip_address}:${device.vnc_port})`, 'text-red-400');
                }
            }
        }

        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

        this.batchSummary = {
            total: this.allDevices.length,
            online: onlineCount,
            offline: offlineCount,
            time: timeStr,
        };

        if (openTerminal) {
            await this.typeLine('------------------------------------------------------------------', 'text-slate-600');
            await this.typeLine(`✔ Pengecekan Selesai: ${onlineCount} Online, ${offlineCount} Offline.`, onlineCount > 0 ? 'text-emerald-400 font-bold' : 'text-amber-400 font-bold');
            this.term.running = false;
            this.scrollTerm();
        }

        this.checkingAll = false;
    },

    dismissBatchSummary() {
        this.batchSummary = null;
    },

    openDetailModal(comp) {
        this.selectedDevice = comp;
        this.detailModalOpen = true;
    },

    closeDetailModal() {
        this.detailModalOpen = false;
        this.selectedDevice = null;
    },

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
        const statusObj = this.statuses[id];

        if (statusObj === undefined) {
            return 'bg-gray-300';
        }

        const vncOk = typeof statusObj === 'object' ? statusObj.vnc : statusObj;
        return vncOk ? 'bg-green-500' : 'bg-red-500';
    },

    statusLabel(id) {
        const statusObj = this.statuses[id];

        if (statusObj === undefined) {
            return '—';
        }

        const vncOk = typeof statusObj === 'object' ? statusObj.vnc : statusObj;
        return vncOk ? 'Online' : 'Offline';
    },

    isSshOpen(comp) {
        if (!comp) return false;
        const statusObj = this.statuses[comp.id];
        if (statusObj && typeof statusObj === 'object' && statusObj.ssh !== undefined) {
            return Boolean(statusObj.ssh);
        }
        return Boolean(comp.ssh_open);
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
