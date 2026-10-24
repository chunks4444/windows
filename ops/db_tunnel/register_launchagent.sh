#!/bin/bash
# macOS 로그인 시 DB SSH 터널이 자동으로 뜨도록 LaunchAgent에 등록한다. 1회 실행.
#   bash ops/db_tunnel/register_launchagent.sh
#
# Windows의 register_task.ps1과 같은 역할. 터널이 죽어도(네트워크 끊김, 잠자기 등) launchd가
# KeepAlive로 다시 띄운다(ThrottleInterval 초 간격). 로그: ~/Library/Logs/pyeongmok-dbtunnel.log
#
# 해제: launchctl bootout gui/$(id -u)/com.pyeongmok.dbtunnel && rm ~/Library/LaunchAgents/com.pyeongmok.dbtunnel.plist

LABEL="com.pyeongmok.dbtunnel"
SCRIPT_PATH="$(cd "$(dirname "$0")" && pwd)/start_tunnel.sh"
PLIST="$HOME/Library/LaunchAgents/$LABEL.plist"
LOG="$HOME/Library/Logs/pyeongmok-dbtunnel.log"

mkdir -p "$HOME/Library/LaunchAgents"
cat > "$PLIST" <<EOF
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>Label</key>
    <string>$LABEL</string>
    <key>ProgramArguments</key>
    <array>
        <string>/bin/bash</string>
        <string>$SCRIPT_PATH</string>
    </array>
    <key>RunAtLoad</key>
    <true/>
    <key>KeepAlive</key>
    <true/>
    <key>ThrottleInterval</key>
    <integer>30</integer>
    <key>StandardOutPath</key>
    <string>$LOG</string>
    <key>StandardErrorPath</key>
    <string>$LOG</string>
</dict>
</plist>
EOF

# 이미 등록돼 있으면 내렸다가 다시 올린다 (스크립트 경로가 바뀐 경우 대비)
launchctl bootout "gui/$(id -u)/$LABEL" 2>/dev/null
launchctl bootstrap "gui/$(id -u)" "$PLIST"

echo "등록 완료: '$LABEL' 로그인 시 자동 실행 (지금 바로 시작됨)"
echo "상태 확인: lsof -nP -iTCP:13306 -sTCP:LISTEN"
