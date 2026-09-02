import RFB from "@novnc/novnc";

// X11 keysyms used by noVNC
const KEY = {
    SUPER: 0xffeb,
    ALT: 0xffe9,
    CTRL: 0xffe5,
    TAB: 0xff09,
    ESC: 0xff1b,
    BACKSPACE: 0xff08,
    ENTER: 0xff0d,
};

const el = {};

function qs(id) {
    return document.getElementById(id);
}

function setStatus(text, persistent = false) {
    if (!el.connStatus) {
        return;
    }

    el.connStatus.textContent = text;
    clearTimeout(setStatus._timer);

    if (!persistent && text !== "") {
        setStatus._timer = setTimeout(() => {
            el.connStatus.textContent = "";
        }, 2000);
    }
}

let state = null;

function createSessionState() {
    return {
        rfb: null,
        connected: false,
        viewOnly: false,
        authTried: false,
        authOk: false,
        userNavigating: false,
    };
}

/* ------------------------------------------------------------------ */
/* Bounded Mobile Virtual Pointer & Controls (Mobile Only)             */
/* ------------------------------------------------------------------ */

// Active exclusively on mobile screens (< 1024px with touch capability)
const isMobileMode = () =>
    window.innerWidth < 1024 &&
    ("ontouchstart" in window || navigator.maxTouchPoints > 0);

let trackpadState = {
    cursorX: 500,
    cursorY: 300,
    touchStartX: 0,
    touchStartY: 0,
    cursorStartX: 500,
    cursorStartY: 300,
    isDragging: false,
    lastTapTime: 0,
};

let mobileZoomLevel = 1.0;

function setMobileZoom(level) {
    mobileZoomLevel = Math.max(
        1.0,
        Math.min(3.0, parseFloat(level.toFixed(2))),
    );

    if (el.mbZoomText) {
        el.mbZoomText.textContent =
            mobileZoomLevel === 1.0 ? "Zoom 1x" : `Zoom ${mobileZoomLevel}x`;
    }

    if (el.screen) {
        if (mobileZoomLevel === 1.0) {
            el.screen.style.width = "100%";
            el.screen.style.height = "100%";
            el.screen.style.transform = "none";
            if (el.screenContainer) {
                el.screenContainer.style.overflow = "hidden";
                el.screenContainer.scrollLeft = 0;
                el.screenContainer.scrollTop = 0;
            }
        } else {
            el.screen.style.width = `${mobileZoomLevel * 100}%`;
            el.screen.style.height = `${mobileZoomLevel * 100}%`;
            el.screen.style.transform = "none";
            if (el.screenContainer) {
                el.screenContainer.style.overflow = "auto";
            }
        }
    }

    updateMobileCursorUI();
}

function updateMobileCursorUI() {
    if (!isMobileMode() || !state?.rfb || !state.rfb._fbWidth) {
        el.virtualCursor?.classList.add("hidden");
        el.mobileDock?.classList.add("hidden");
        el.mobileDock?.classList.remove("flex");
        return;
    }

    el.mobileDock?.classList.remove("hidden");
    el.mobileDock?.classList.add("flex");

    if (!el.virtualCursor) return;

    const canvas =
        el.screen?.querySelector("canvas") || state.rfb._canvas || el.screen;
    if (!canvas) return;

    const canvasRect = canvas.getBoundingClientRect();
    const containerRect = (
        el.screenContainer || el.screen
    ).getBoundingClientRect();

    const fbW = state.rfb._fbWidth;
    const fbH = state.rfb._fbHeight;

    if (
        fbW <= 0 ||
        fbH <= 0 ||
        canvasRect.width <= 0 ||
        canvasRect.height <= 0
    ) {
        return;
    }

    // Clamp virtual cursor position strictly inside [0, fbWidth] and [0, fbHeight]
    trackpadState.cursorX = Math.max(0, Math.min(fbW, trackpadState.cursorX));
    trackpadState.cursorY = Math.max(0, Math.min(fbH, trackpadState.cursorY));

    // Calculate position strictly relative to the container element
    const canvasOffsetX = canvasRect.left - containerRect.left;
    const canvasOffsetY = canvasRect.top - containerRect.top;

    const cursorLeft =
        canvasOffsetX + (trackpadState.cursorX / fbW) * canvasRect.width;
    const cursorTop =
        canvasOffsetY + (trackpadState.cursorY / fbH) * canvasRect.height;

    el.virtualCursor.style.left = `${cursorLeft}px`;
    el.virtualCursor.style.top = `${cursorTop}px`;
    el.virtualCursor.classList.remove("hidden");

    // Auto-scroll screen container if cursor moves near viewport boundaries in zoom mode
    if (mobileZoomLevel > 1.0 && el.screenContainer) {
        const cursorLeftPx = (trackpadState.cursorX / fbW) * canvasRect.width;
        const cursorTopPx = (trackpadState.cursorY / fbH) * canvasRect.height;
        const viewWidth = el.screenContainer.clientWidth;
        const viewHeight = el.screenContainer.clientHeight;

        if (cursorLeftPx < el.screenContainer.scrollLeft + 40) {
            el.screenContainer.scrollLeft = Math.max(0, cursorLeftPx - 40);
        } else if (
            cursorLeftPx >
            el.screenContainer.scrollLeft + viewWidth - 40
        ) {
            el.screenContainer.scrollLeft = cursorLeftPx - viewWidth + 40;
        }

        if (cursorTopPx < el.screenContainer.scrollTop + 40) {
            el.screenContainer.scrollTop = Math.max(0, cursorTopPx - 40);
        } else if (
            cursorTopPx >
            el.screenContainer.scrollTop + viewHeight - 40
        ) {
            el.screenContainer.scrollTop = cursorTopPx - viewHeight + 40;
        }
    }
}

function getCanvasElementCoordinates() {
    if (!state?.rfb) return { elementX: 0, elementY: 0 };

    const canvas =
        el.screen?.querySelector("canvas") || state.rfb._canvas || el.screen;
    if (!canvas) return { elementX: 0, elementY: 0 };

    const canvasRect = canvas.getBoundingClientRect();
    const fbW = state.rfb._fbWidth || 1024;
    const fbH = state.rfb._fbHeight || 768;

    if (canvasRect.width <= 0 || canvasRect.height <= 0)
        return { elementX: 0, elementY: 0 };

    const elementX = (trackpadState.cursorX / fbW) * canvasRect.width;
    const elementY = (trackpadState.cursorY / fbH) * canvasRect.height;

    return { elementX, elementY };
}

function sendMousePointer(buttonMask) {
    if (!state?.rfb) return;

    const { elementX, elementY } = getCanvasElementCoordinates();

    if (typeof state.rfb._sendMouse === "function") {
        try {
            state.rfb._sendMouse(elementX, elementY, buttonMask);
        } catch (e) {
            console.error("_sendMouse error:", e);
        }
    }
}

function triggerMouseClick(type = "left") {
    if (!state?.rfb) return;

    const buttonMask = type === "right" ? 4 : type === "middle" ? 2 : 1;

    if (type === "double") {
        // Double Click sequence
        sendMousePointer(0);
        setTimeout(() => {
            sendMousePointer(1);
            setTimeout(() => {
                sendMousePointer(0);
                setTimeout(() => {
                    sendMousePointer(1);
                    setTimeout(() => {
                        sendMousePointer(0);
                    }, 80);
                }, 60);
            }, 80);
        }, 30);
    } else {
        // Single Click sequence
        sendMousePointer(0);
        setTimeout(() => {
            sendMousePointer(buttonMask);
            setTimeout(() => {
                sendMousePointer(0);
            }, 90);
        }, 30);
    }

    if (el.virtualCursor && isMobileMode()) {
        el.virtualCursor.classList.add("scale-150");
        setTimeout(() => {
            el.virtualCursor?.classList.remove("scale-150");
        }, 200);
    }
}

let initialPinchDistance = 0;
let initialZoomOnPinch = 1.0;

function getPinchDistance(touches) {
    const dx = touches[0].clientX - touches[1].clientX;
    const dy = touches[0].clientY - touches[1].clientY;
    return Math.hypot(dx, dy);
}

function bindMobileTrackpad() {
    if (!el.screen) return;

    let touchStartTime = 0;

    // Capture phase touch event listeners (active ONLY in mobile mode)
    el.screen.addEventListener(
        "touchstart",
        (e) => {
            if (!isMobileMode() || !state?.rfb) return;

            if (e.touches.length === 2) {
                initialPinchDistance = getPinchDistance(e.touches);
                initialZoomOnPinch = mobileZoomLevel;
                return;
            }

            if (e.touches.length > 1) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            if (e.touches.length === 1) {
                const touch = e.touches[0];
                trackpadState.touchStartX = touch.clientX;
                trackpadState.touchStartY = touch.clientY;
                trackpadState.cursorStartX = trackpadState.cursorX;
                trackpadState.cursorStartY = trackpadState.cursorY;
                touchStartTime = Date.now();
                trackpadState.isDragging = false;
            }
        },
        { capture: true, passive: false },
    );

    el.screen.addEventListener(
        "touchmove",
        (e) => {
            if (!isMobileMode() || !state?.rfb) return;

            if (e.touches.length === 2) {
                const currentDistance = getPinchDistance(e.touches);
                if (initialPinchDistance > 0 && currentDistance > 0) {
                    const scale = currentDistance / initialPinchDistance;
                    const newZoom = initialZoomOnPinch * scale;
                    setMobileZoom(newZoom);
                }
                return;
            }

            if (e.touches.length > 1) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            if (e.touches.length === 1) {
                const touch = e.touches[0];
                const dx = touch.clientX - trackpadState.touchStartX;
                const dy = touch.clientY - trackpadState.touchStartY;

                if (Math.abs(dx) > 4 || Math.abs(dy) > 4) {
                    trackpadState.isDragging = true;
                }

                const canvas =
                    el.screen.querySelector("canvas") ||
                    state.rfb._canvas ||
                    el.screen;
                const canvasRect = canvas.getBoundingClientRect();
                const fbW = state.rfb._fbWidth || 1024;
                const fbH = state.rfb._fbHeight || 768;

                if (canvasRect.width > 0 && canvasRect.height > 0) {
                    const remoteDx = (dx / canvasRect.width) * fbW * 1.2;
                    const remoteDy = (dy / canvasRect.height) * fbH * 1.2;

                    trackpadState.cursorX = Math.max(
                        0,
                        Math.min(fbW, trackpadState.cursorStartX + remoteDx),
                    );
                    trackpadState.cursorY = Math.max(
                        0,
                        Math.min(fbH, trackpadState.cursorStartY + remoteDy),
                    );

                    sendMousePointer(0);
                    updateMobileCursorUI();
                }
            }
        },
        { capture: true, passive: false },
    );

    el.screen.addEventListener(
        "touchend",
        (e) => {
            if (!isMobileMode() || !state?.rfb) return;

            if (e.touches.length > 0) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            const duration = Date.now() - touchStartTime;

            if (!trackpadState.isDragging && duration < 300) {
                const now = Date.now();
                const timeSinceLastTap = now - trackpadState.lastTapTime;
                trackpadState.lastTapTime = now;

                if (timeSinceLastTap > 50 && timeSinceLastTap < 300) {
                    triggerMouseClick("double");
                } else {
                    const clickType =
                        e.changedTouches.length > 1 ? "right" : "left";
                    triggerMouseClick(clickType);
                }
            }
        },
        { capture: true, passive: false },
    );

    window.addEventListener("resize", updateMobileCursorUI);
}

/* ------------------------------------------------------------------ */
/* Overlay states                                                      */
/* ------------------------------------------------------------------ */

function showConnecting() {
    el.connecting.classList.remove("hidden");
    el.connecting.classList.add("flex");
    el.disconnected.classList.add("hidden");
    el.disconnected.classList.remove("flex");

    const steps = [
        "Fetching session ticket...",
        "Opening WebSocket tunnel...",
        "Negotiating VNC handshake...",
    ];

    let index = 0;
    el.stageText.textContent = steps[0];

    clearInterval(showConnecting._timer);
    showConnecting._timer = setInterval(() => {
        if (index < steps.length - 1) {
            el.stageText.textContent = steps[++index];
        }
    }, 1200);
}

function hideOverlays() {
    clearInterval(showConnecting._timer);
    el.connecting.classList.add("hidden");
    el.connecting.classList.remove("flex");
    el.disconnected.classList.add("hidden");
    el.disconnected.classList.remove("flex");
}

function showDisconnected(message, isAuthError = false) {
    if (state?.userNavigating) {
        return;
    }

    clearInterval(showConnecting._timer);
    el.connecting.classList.add("hidden");
    el.connecting.classList.remove("flex");
    el.passwordOverlay.classList.add("hidden");
    el.passwordOverlay.classList.remove("flex");

    el.discText.textContent = message;
    if (isAuthError && el.discBtn) {
        el.discBtn.href = "/login";
        if (el.discBtnText) {
            el.discBtnText.textContent = "Login Ulang";
        }
    } else if (el.discBtn) {
        el.discBtn.href = "/computers";
        if (el.discBtnText) {
            el.discBtnText.textContent = "Kembali ke Daftar Devices";
        }
    }

    el.disconnected.classList.remove("hidden");
    el.disconnected.classList.add("flex");
}

function showPasswordPrompt(error = "") {
    el.connecting.classList.add("hidden");
    el.connecting.classList.remove("flex");
    el.disconnected.classList.add("hidden");
    el.disconnected.classList.remove("flex");

    el.passwordError.textContent = error;

    if (error) {
        el.passwordError.classList.remove("hidden");
    } else {
        el.passwordError.classList.add("hidden");
    }

    el.passwordInput.value = "";
    el.passwordOverlay.classList.remove("hidden");
    el.passwordOverlay.classList.add("flex");
    setTimeout(() => el.passwordInput.focus(), 50);
}

/* ------------------------------------------------------------------ */
/* Connection                                                          */
/* ------------------------------------------------------------------ */

async function fetchTicket() {
    const response = await fetch(el.root.dataset.ticketUrl, {
        headers: { Accept: "application/json" },
    });

    if (response.status === 401 || response.status === 419) {
        throw new Error("unauthenticated");
    }

    if (!response.ok) {
        throw new Error("ticket_unavailable");
    }

    return response.json();
}

function applyScaling(enabled) {
    if (state.rfb) {
        state.rfb.scaleViewport = enabled;
        if (isMobileMode()) setTimeout(updateMobileCursorUI, 100);
    }
}

function applyLocalCursor(enabled) {
    if (state?.rfb) {
        try {
            state.rfb.showDotCursor = enabled;
        } catch (e) {}
    }

    if (enabled) {
        document.body.classList.add("local-cursor-active");
    } else {
        document.body.classList.remove("local-cursor-active");
    }

    const canvas = el.screen?.querySelector("canvas");
    if (canvas) {
        if (enabled) {
            canvas.style.setProperty("cursor", "default", "important");
        } else {
            canvas.style.removeProperty("cursor");
        }
    }
}

function applyViewOnly(enabled) {
    if (state.rfb) {
        state.rfb.viewOnly = enabled;
    }

    el.btnViewOnly?.classList.toggle("bg-[#00828c]", enabled);
    el.btnViewOnly?.classList.toggle("text-white", enabled);
}

function disconnect(message) {
    if (state?.userNavigating) {
        return;
    }

    if (state?.rfb) {
        try {
            state.rfb.disconnect();
        } catch {}
        state.rfb = null;
    }

    if (!state?.connected) {
        message ??=
            "Koneksi gagal. Pastikan komputer target aktif dan service websockify berjalan.";
    }

    if (state) {
        state.connected = false;
    }

    if (message) {
        showDisconnected(message);
    }

    setStatus("");
}

function connect(ticket) {
    if (state.rfb) {
        try {
            state.rfb.disconnect();
        } catch (e) {}
        state.rfb = null;
    }

    state.ticket = ticket;
    const options = { shared: true };

    if (ticket.password) {
        options.credentials = { password: ticket.password };
        state.authTried = true;
    }

    state.rfb = new RFB(el.screen, ticket.ws_url, options);
    state.deviceName = ticket.device_name || "remote";

    state.rfb.scaleViewport = true; // auto-fit by default

    // Automatically enable local cursor on desktop mode, keep default (false) for mobile mode
    const enableLocalCursor = !isMobileMode();
    applyLocalCursor(enableLocalCursor);
    if (el.chkCursor) {
        el.chkCursor.checked = enableLocalCursor;
    }
    state.rfb.addEventListener("connect", () => {
        state.connected = true;
        state.authOk = true;
        el.passwordOverlay.classList.add("hidden");
        el.passwordOverlay.classList.remove("flex");
        hideOverlays();
        setStatus(`Terhubung — ${state.deviceName}`);

        // Re-apply local cursor styling once canvas is created and connected
        applyLocalCursor(el.chkCursor ? el.chkCursor.checked : !isMobileMode());

        if (isMobileMode() && state.rfb._fbWidth && state.rfb._fbHeight) {
            trackpadState.cursorX = Math.round(state.rfb._fbWidth / 2);
            trackpadState.cursorY = Math.round(state.rfb._fbHeight / 2);
            updateMobileCursorUI();
        }
    });

    state.rfb.addEventListener("disconnect", (event) => {
        if (state?.userNavigating) {
            return;
        }

        state.rfb = null;
        const wasClean = event.detail?.clean === true;

        if (!wasClean && state.authTried && !state.authOk) {
            showPasswordPrompt("Password VNC salah atau ditolak server remote");
            state.authTried = false;
            return;
        }

        disconnect(
            wasClean
                ? "Sesi remote VNC diakhiri."
                : "Koneksi VNC terputus (Cek server VNC / Port 5900 di target).",
        );
    });

    state.rfb.addEventListener("credentialsrequired", () => {
        state.authTried = true;
        showPasswordPrompt(
            "Server VNC meminta password. Masukkan password VNC perangkat ini.",
        );
    });

    let lastHostClipboard = "";
    let lastRemoteClipboard = "";

    // 1. REMOTE -> HOST: Copy on Remote Windows -> Paste on Host (Arch Linux)
    state.rfb.addEventListener("clipboard", (event) => {
        const text = event.detail?.text;
        if (!text || text === lastRemoteClipboard) return;

        lastRemoteClipboard = text;

        if (el.clipboardTextInput) {
            el.clipboardTextInput.value = text;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard
                .writeText(text)
                .then(() => {
                    setStatus(
                        "Clipboard dari Remote Windows disalin ke Host (Siap paste di Arch)!",
                    );
                })
                .catch(() => {
                    setStatus(
                        "Teks dari Remote Windows diterima (Lihat menu Clipboard Toolbar)",
                    );
                });
        } else {
            setStatus(
                "Teks dari Remote Windows diterima (Lihat menu Clipboard Toolbar)",
            );
        }
    });

    // 2. HOST -> REMOTE: Auto-sync Host clipboard to Remote VNC on click / focus
    async function syncHostClipboardToRemote() {
        if (!state?.rfb || state.viewOnly) return;

        try {
            if (navigator.clipboard && navigator.clipboard.readText) {
                const text = await navigator.clipboard.readText();
                if (text && text !== lastHostClipboard) {
                    lastHostClipboard = text;
                    state.rfb.clipboardPasteFrom(text);
                    if (el.clipboardTextInput) {
                        el.clipboardTextInput.value = text;
                    }
                    setStatus("Clipboard Host disinkronkan ke Remote Windows");
                }
            }
        } catch (e) {
            // Ignore background readText permissions if denied by browser
        }
    }

    el.screen?.addEventListener("pointerdown", syncHostClipboardToRemote);
    window.addEventListener("focus", syncHostClipboardToRemote);

    // 3. HOST -> REMOTE: Paste event (Ctrl+V on Host)
    window.addEventListener("paste", (event) => {
        if (!state?.rfb || state.viewOnly) return;
        const text = event.clipboardData?.getData("text");
        if (text) {
            lastHostClipboard = text;
            state.rfb.clipboardPasteFrom(text);
            if (el.clipboardTextInput) {
                el.clipboardTextInput.value = text;
            }
            setTimeout(sendCtrlV, 100);
            setStatus("Teks dipaste & dikirim ke Remote Windows");
        }
    });
}

/* ------------------------------------------------------------------ */
/* Quick keys                                                          */
/* ------------------------------------------------------------------ */

function sendKeySequence(keys) {
    if (!state?.rfb || state.viewOnly) {
        return;
    }

    let delay = 0;

    keys.forEach(([keysym, down]) => {
        setTimeout(() => {
            if (state?.rfb) {
                try {
                    state.rfb.sendKey(keysym, null, down);
                } catch {
                    try {
                        state.rfb.sendKey(keysym, down);
                    } catch (e) {
                        console.error("sendKey failed:", e);
                    }
                }
            }
        }, delay);
        delay += 25;
    });
}

const QUICK_KEYS = {
    ctrlAltDel: () => {
        if (!state?.rfb || state.viewOnly) return;
        try {
            state.rfb.sendCtrlAltDel();
        } catch {
            sendKeySequence([
                [0xffe5, true],
                [0xffe9, true],
                [0xffff, true],
                [0xffff, false],
                [0xffe9, false],
                [0xffe5, false],
            ]);
        }
        setStatus("Sent Ctrl + Alt + Del");
    },
    winKey: () => {
        sendKeySequence([
            [KEY.SUPER, true],
            [KEY.SUPER, false],
        ]);
        setStatus("Sent Win / Super Key");
    },
    altTab: () => {
        sendKeySequence([
            [KEY.ALT, true],
            [KEY.TAB, true],
            [KEY.TAB, false],
            [KEY.ALT, false],
        ]);
        setStatus("Sent Alt + Tab");
    },
    ctrlEsc: () => {
        sendKeySequence([
            [KEY.CTRL, true],
            [KEY.ESC, true],
            [KEY.ESC, false],
            [KEY.CTRL, false],
        ]);
        setStatus("Sent Ctrl + Esc");
    },
    f5: () => {
        sendKeySequence([
            [0xffc2, true], // F5
            [0xffc2, false],
        ]);
        setStatus("Sent F5 (Refresh)");
    },
    ctrlAltBackspace: () => {
        sendKeySequence([
            [KEY.CTRL, true],
            [KEY.ALT, true],
            [0xff08, true], // Backspace
            [0xff08, false],
            [KEY.ALT, false],
            [KEY.CTRL, false],
        ]);
        setStatus("Sent Ctrl + Alt + Backspace");
    },
};

/* ------------------------------------------------------------------ */
/* Toolbar & Mobile Controls Binding                                  */
/* ------------------------------------------------------------------ */

function screenshot() {
    if (!state?.rfb) {
        return;
    }

    const link = document.createElement("a");
    const stamp = new Date().toISOString().replace(/[:.]/g, "-").slice(0, 19);

    link.href = state.rfb.toDataURL();
    link.download = `${state.deviceName}-${stamp}.png`;
    link.click();

    setStatus("Screenshot saved");
}

function bindToolbar() {
    el.btnToggleMenu?.addEventListener("click", (event) => {
        event.stopPropagation();
        el.toolbarMenu?.classList.toggle("hidden");
    });

    el.btnBack?.addEventListener("click", (e) => {
        e.preventDefault();
        if (state) {
            state.userNavigating = true;
        }
        if (state?.rfb) {
            try {
                state.rfb.disconnect();
            } catch {}
            state.rfb = null;
        }
        window.location.href = "/computers";
    });

    el.btnPasswordBack?.addEventListener("click", (e) => {
        e.preventDefault();
        if (state) {
            state.userNavigating = true;
        }
        if (state?.rfb) {
            try {
                state.rfb.disconnect();
            } catch {}
            state.rfb = null;
        }
        window.location.href = "/computers";
    });

    el.passwordForm?.addEventListener("submit", (event) => {
        event.preventDefault();

        const password = el.passwordInput.value;

        if (!password || !state?.ticket) {
            return;
        }

        showConnecting();
        el.passwordOverlay.classList.add("hidden");
        el.passwordOverlay.classList.remove("flex");
        setStatus("Mengautentikasi VNC...");

        const updatedTicket = { ...state.ticket, password: password };
        connect(updatedTicket);
    });

    el.btnQuickKeys?.addEventListener("click", (event) => {
        event.stopPropagation();
        el.quickKeysPanel?.classList.toggle("hidden");
    });

    document.addEventListener("click", (event) => {
        if (!el.toolbar?.contains(event.target)) {
            el.quickKeysPanel?.classList.add("hidden");
            el.clipboardPanel?.classList.add("hidden");
            el.settingsPanel?.classList.add("hidden");
        } else {
            if (
                !el.quickKeysPanel?.contains(event.target) &&
                event.target !== el.btnQuickKeys
            ) {
                el.quickKeysPanel?.classList.add("hidden");
            }
            if (
                !el.clipboardPanel?.contains(event.target) &&
                event.target !== el.btnClipboard
            ) {
                el.clipboardPanel?.classList.add("hidden");
            }
            if (
                !el.settingsPanel?.contains(event.target) &&
                event.target !== el.btnSettings
            ) {
                el.settingsPanel?.classList.add("hidden");
            }
        }
    });

    function sendCtrlV() {
        if (!state?.rfb || state.viewOnly) return;
        const CTRL_KEY = 0xffe3; // Control_L
        const V_KEY = 0x0076; // v

        try {
            state.rfb.sendKey(CTRL_KEY, "ControlLeft", true);
            setTimeout(() => {
                state.rfb.sendKey(V_KEY, "KeyV", true);
                setTimeout(() => {
                    state.rfb.sendKey(V_KEY, "KeyV", false);
                    setTimeout(() => {
                        state.rfb.sendKey(CTRL_KEY, "ControlLeft", false);
                    }, 20);
                }, 20);
            }, 20);
        } catch (e) {
            console.error("sendCtrlV failed:", e);
        }
    }

    function typeTextToRemote(text) {
        if (!state?.rfb || state.viewOnly) return;
        let delay = 0;
        for (let i = 0; i < text.length; i++) {
            const char = text.charAt(i);
            let keysym = char.charCodeAt(0);
            if (char === "\n" || char === "\r") keysym = 0xff0d; // Enter key
            if (char === "\t") keysym = 0xff09; // Tab key

            setTimeout(() => {
                try {
                    state.rfb.sendKey(keysym, null, true);
                    setTimeout(() => {
                        state.rfb.sendKey(keysym, null, false);
                    }, 15);
                } catch (e) {
                    console.error("typeTextToRemote failed:", e);
                }
            }, delay);
            delay += 35;
        }
    }

    el.btnClipboard?.addEventListener("click", (event) => {
        event.stopPropagation();
        el.clipboardPanel?.classList.toggle("hidden");
        if (!el.clipboardPanel?.classList.contains("hidden")) {
            setTimeout(() => el.clipboardTextInput?.focus(), 50);
        }
    });

    el.btnSendClipboard?.addEventListener("click", () => {
        const text = el.clipboardTextInput?.value;
        if (text && state?.rfb) {
            state.rfb.clipboardPasteFrom(text);
            setTimeout(sendCtrlV, 150);
            setStatus("Teks dikirim ke Clipboard & Ctrl+V otomatis");
            el.clipboardPanel?.classList.add("hidden");
        }
    });

    el.btnTypeClipboard?.addEventListener("click", () => {
        const text = el.clipboardTextInput?.value;
        if (text && state?.rfb && !state.viewOnly) {
            typeTextToRemote(text);
            setStatus("Mengetikkan teks langsung ke layar VNC Remote...");
            el.clipboardPanel?.classList.add("hidden");
        }
    });

    el.qkCtrlAltDel?.addEventListener("click", () => {
        QUICK_KEYS.ctrlAltDel();
        el.quickKeysPanel?.classList.add("hidden");
    });

    el.qkWinKey?.addEventListener("click", () => {
        QUICK_KEYS.winKey();
        el.quickKeysPanel?.classList.add("hidden");
    });

    el.qkAltTab?.addEventListener("click", () => {
        QUICK_KEYS.altTab();
        el.quickKeysPanel?.classList.add("hidden");
    });

    el.qkCtrlEsc?.addEventListener("click", () => {
        QUICK_KEYS.ctrlEsc();
        el.quickKeysPanel?.classList.add("hidden");
    });

    el.qkF5?.addEventListener("click", () => {
        QUICK_KEYS.f5();
        el.quickKeysPanel?.classList.add("hidden");
    });

    el.qkCtrlAltBackspace?.addEventListener("click", () => {
        QUICK_KEYS.ctrlAltBackspace();
        el.quickKeysPanel?.classList.add("hidden");
    });

    el.btnViewOnly?.addEventListener("click", () => {
        state.viewOnly = !state.viewOnly;
        applyViewOnly(state.viewOnly);
        setStatus(state.viewOnly ? "View-only mode" : "Control mode");
    });

    el.btnScreenshot?.addEventListener("click", screenshot);

    el.btnFullscreen?.addEventListener("click", () => {
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else {
            document.documentElement.requestFullscreen();
        }
    });

    el.btnSettings?.addEventListener("click", (event) => {
        event.stopPropagation();
        el.settingsPanel?.classList.toggle("hidden");
    });

    el.btnToggleBar?.addEventListener("click", () => {
        el.toolbar?.classList.add("lg:hidden");
        el.floatingShowBar?.classList.remove("hidden");
        setTimeout(() => window.dispatchEvent(new Event("resize")), 50);
    });

    el.floatingShowBar?.addEventListener("click", () => {
        el.toolbar?.classList.remove("lg:hidden");
        el.floatingShowBar?.classList.add("hidden");
        setTimeout(() => window.dispatchEvent(new Event("resize")), 50);
    });

    el.chkScale?.addEventListener("change", () =>
        applyScaling(el.chkScale.checked),
    );
    el.chkCursor?.addEventListener("change", () =>
        applyLocalCursor(el.chkCursor.checked),
    );

    // --- Mobile Dock Button Handlers (Mobile Only) ---
    el.mbLeftClick?.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        triggerMouseClick("left");
        setStatus("Klik Kiri");
    });

    el.mbDoubleClick?.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        triggerMouseClick("double");
        setStatus("Klik 2x");
    });

    el.mbRightClick?.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        triggerMouseClick("right");
        setStatus("Klik Kanan");
    });

    el.mbZoom?.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        let nextZoom = 1.0;
        if (mobileZoomLevel < 1.4) nextZoom = 1.5;
        else if (mobileZoomLevel < 1.9) nextZoom = 2.0;
        else nextZoom = 1.0;
        setMobileZoom(nextZoom);
        setStatus(`Zoom ${nextZoom === 1.0 ? "1x (Fit)" : nextZoom + "x"}`);
    });

    function updateKeyboardDockState(active) {
        if (!el.mbKeyboard) return;
        const iconEl = qs("mb-keyboard-icon");
        if (active) {
            el.mbKeyboard.classList.remove(
                "bg-emerald-500/20",
                "text-emerald-400",
                "border-emerald-500/40",
            );
            el.mbKeyboard.classList.add(
                "bg-rose-500/30",
                "text-rose-300",
                "border-rose-500/50",
            );
            el.mbKeyboard.title = "Tutup Keyboard (Exit Ketik)";
            if (iconEl) {
                iconEl.innerHTML =
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />';
            }
        } else {
            el.mbKeyboard.classList.remove(
                "bg-rose-500/30",
                "text-rose-300",
                "border-rose-500/50",
            );
            el.mbKeyboard.classList.add(
                "bg-emerald-500/20",
                "text-emerald-400",
                "border-emerald-500/40",
            );
            el.mbKeyboard.title = "Buka Keyboard Android";
            if (iconEl) {
                iconEl.innerHTML =
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />';
            }
        }
    }

    el.mbKeyboard?.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (!el.mobileKeyboardInput) return;

        if (document.activeElement === el.mobileKeyboardInput) {
            el.mobileKeyboardInput.blur();
            updateKeyboardDockState(false);
            setStatus("Keyboard Ditutup");
        } else {
            el.mobileKeyboardInput.focus();
            updateKeyboardDockState(true);
            setStatus("Papan Ketik Android Aktif (Klik X untuk Tutup)");
        }
    });

    if (el.mobileKeyboardInput) {
        el.mobileKeyboardInput.addEventListener("focus", () =>
            updateKeyboardDockState(true),
        );
        el.mobileKeyboardInput.addEventListener("blur", () => {
            el.mobileKeyboardInput.value = "";
            updateKeyboardDockState(false);
        });

        el.mobileKeyboardInput.addEventListener("beforeinput", (e) => {
            if (!state?.rfb || state.viewOnly) return;
            if (e.inputType === "deleteContentBackward") {
                sendKeySequence([
                    [KEY.BACKSPACE, true],
                    [KEY.BACKSPACE, false],
                ]);
            }
        });

        el.mobileKeyboardInput.addEventListener("input", (e) => {
            if (!state?.rfb || state.viewOnly) return;
            const val = el.mobileKeyboardInput.value;
            if (val) {
                for (let i = 0; i < val.length; i++) {
                    const char = val.charAt(i);
                    let code = char.charCodeAt(0);
                    if (char === "\n" || char === "\r") code = KEY.ENTER;
                    try {
                        state.rfb.sendKey(code, null, true);
                        state.rfb.sendKey(code, null, false);
                    } catch (err) {
                        console.error("sendKey char error:", err);
                    }
                }
                el.mobileKeyboardInput.value = "";
            }
        });

        el.mobileKeyboardInput.addEventListener("keydown", (e) => {
            if (!state?.rfb || state.viewOnly) return;
            if (e.key === "Backspace") {
                sendKeySequence([
                    [KEY.BACKSPACE, true],
                    [KEY.BACKSPACE, false],
                ]);
            } else if (e.key === "Enter") {
                sendKeySequence([
                    [KEY.ENTER, true],
                    [KEY.ENTER, false],
                ]);
            }
        });
    }

    bindMobileTrackpad();
}

/* ------------------------------------------------------------------ */
/* Init                                                                */
/* ------------------------------------------------------------------ */

async function init() {
    el.root = qs("viewer-root");
    if (!el.root) {
        return;
    }

    el.screenContainer = qs("screen-container");
    el.screen = qs("screen");
    el.toolbar = qs("toolbar");
    el.btnToggleMenu = qs("btn-toggle-menu");
    el.toolbarMenu = qs("toolbar-menu");
    el.connStatus = qs("conn-status");
    el.connecting = qs("overlay-connecting");
    el.stageText = qs("stage-text");
    el.disconnected = qs("overlay-disconnected");
    el.discText = qs("disc-text");
    el.discBtn = qs("disc-btn");
    el.discBtnText = qs("disc-btn-text");
    el.passwordOverlay = qs("overlay-password");
    el.passwordForm = qs("password-form");
    el.passwordInput = qs("vnc-password-input");
    el.passwordError = qs("password-error");
    el.btnPasswordBack = qs("btn-password-back");
    el.btnBack = qs("btn-back");
    el.btnQuickKeys = qs("btn-quick-keys");
    el.quickKeysPanel = qs("quick-keys-panel");
    el.qkCtrlAltDel = qs("qk-ctrl-alt-del");
    el.qkWinKey = qs("qk-win-key");
    el.qkAltTab = qs("qk-alt-tab");
    el.qkCtrlEsc = qs("qk-ctrl-esc");
    el.qkF5 = qs("qk-f5");
    el.qkCtrlAltBackspace = qs("qk-ctrl-alt-backspace");
    el.btnClipboard = qs("btn-clipboard");
    el.clipboardPanel = qs("clipboard-panel");
    el.clipboardTextInput = qs("clipboard-text-input");
    el.btnSendClipboard = qs("btn-send-clipboard");
    el.btnTypeClipboard = qs("btn-type-clipboard");
    el.btnViewOnly = qs("btn-view-only");
    el.btnScreenshot = qs("btn-screenshot");
    el.btnFullscreen = qs("btn-fullscreen");
    el.btnSettings = qs("btn-settings");
    el.settingsPanel = qs("settings-panel");
    el.chkScale = qs("chk-scale");
    el.chkCursor = qs("chk-cursor");
    el.btnToggleBar = qs("btn-toggle-bar");
    el.floatingShowBar = qs("floating-show-bar");

    // Mobile Virtual Pointer & Floating Dock Elements
    el.virtualCursor = qs("virtual-cursor");
    el.mobileDock = qs("mobile-dock");
    el.mbLeftClick = qs("mb-left-click");
    el.mbDoubleClick = qs("mb-double-click");
    el.mbRightClick = qs("mb-right-click");
    el.mbZoom = qs("mb-zoom");
    el.mbZoomText = qs("mb-zoom-text");
    el.mbKeyboard = qs("mb-keyboard");
    el.mobileKeyboardInput = qs("mobile-keyboard-input");

    bindToolbar();

    try {
        showConnecting();
        const ticket = await fetchTicket();

        state = createSessionState();
        connect(ticket);
    } catch (err) {
        if (err.message === "unauthenticated") {
            showDisconnected(
                "Sesi autentikasi Anda telah berakhir (logout). Silakan login kembali untuk mengakses portal VNC.",
                true,
            );
        } else {
            showDisconnected(
                "Gagal memulai sesi remote. Sesi mungkin telah kedaluwarsa — silakan kembali ke halaman Devices dan klik Connect lagi.",
            );
        }
    }
}

init();
