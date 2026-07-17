#!/bin/bash
# 평목 DB 접속용 SSH 터널 (macOS/Linux). MySQL은 컨테이너 안에서만 열려있고 외부 TCP(6836)는
# 막혀 있으므로, 로컬 PHP 개발 서버(src/lib/db.php)가 127.0.0.1:13306으로 접속할 수 있도록
# 이 터널이 떠 있어야 한다.
#
# 최초 1회 설정:
#   1) ssh-keygen -t ed25519 -f ~/.ssh/pyeongmok_studio -C "$(whoami)@$(hostname) (mac)"
#   2) 공개키(~/.ssh/pyeongmok_studio.pub) 내용을 서버 authorized_keys에 등록 (관리자에게 요청)
#
# 작업 중엔 터미널 탭 하나에 이 스크립트를 띄워두거나, macOS라면 로그인 시 자동 실행되도록
# launchd(LaunchAgent)에 등록해서 써도 된다.
#
# 실행: ./start_tunnel.sh

ssh -N \
    -o ServerAliveInterval=30 \
    -o ServerAliveCountMax=3 \
    -o ExitOnForwardFailure=yes \
    -o StrictHostKeyChecking=accept-new \
    -p 6822 \
    -i ~/.ssh/pyeongmok_studio \
    -L 13306:127.0.0.1:3306 \
    chunks@211.35.72.68
