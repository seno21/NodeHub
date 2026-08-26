#!/usr/bin/env bash
#
# Install websockify bridge as a systemd service.
# Paths are detected automatically so this works on any PC.
#
# Usage:  sudo ./scripts/install-bridge-service.sh
#
set -euo pipefail

SERVICE_NAME="webvnc-bridge"
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if [[ $EUID -ne 0 ]]; then
    echo "Run with sudo: sudo $0" >&2
    exit 1
fi

RUN_USER="${SUDO_USER:-root}"

# --- Locate websockify binary (incl. user-local pip installs) ---
find_websockify() {
    if command -v websockify >/dev/null 2>&1; then
        command -v websockify
        return 0
    fi

    local candidate="/home/${RUN_USER}/.local/bin/websockify"
    if [[ -x "$candidate" ]]; then
        echo "$candidate"
        return 0
    fi

    return 1
}

if ! WEBSOCKIFY_BIN="$(find_websockify)"; then
    echo "websockify not found. Install first:  pip install --user websockify" >&2
    exit 1
fi

LISTEN="${VNC_WEBSOCKIFY_LISTEN:-0.0.0.0:6080}"
TOKEN_FILE="${PROJECT_DIR}/storage/app/vnc-tokens.cfg"

echo "Project     : ${PROJECT_DIR}"
echo "Binary      : ${WEBSOCKIFY_BIN}"
echo "Listen      : ${LISTEN}"
echo "Service user: ${RUN_USER}"

# --- Stop any manually started instance of THIS project's bridge ---
pkill -f "websockify.*${TOKEN_FILE}" 2>/dev/null || true
sleep 0.5

# --- Render unit ---
UNIT="/etc/systemd/system/${SERVICE_NAME}.service"

cat > "$UNIT" <<EOF
[Unit]
Description=WebVNC Portal - websockify bridge
After=network-online.target
Wants=network-online.target

[Service]
Type=simple
User=${RUN_USER}
WorkingDirectory=${PROJECT_DIR}
ExecStartPre=/usr/bin/mkdir -p ${PROJECT_DIR}/storage/app
ExecStartPre=/usr/bin/touch ${TOKEN_FILE}
ExecStart=${WEBSOCKIFY_BIN} \\
    --log-file=${PROJECT_DIR}/storage/logs/websockify.log \\
    --token-plugin TokenFile \\
    --token-source ${TOKEN_FILE} \\
    ${LISTEN}
Restart=on-failure
RestartSec=3

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable --now "${SERVICE_NAME}.service"

sleep 1

if systemctl is-active --quiet "${SERVICE_NAME}.service"; then
    echo "OK: ${SERVICE_NAME} is running (${LISTEN}) and starts on boot."
else
    echo "FAILED to start. Check: journalctl -u ${SERVICE_NAME} -e" >&2
    exit 1
fi
