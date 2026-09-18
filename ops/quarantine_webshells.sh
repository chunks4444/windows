#!/bin/bash
# 워드프레스 사이트에 심어진 랜덤 이름 위장 폴더(웹셸)를 ~/quarantine_날짜 로 "이동"(삭제 아님)한다.
# 사용법: bash quarantine_webshells.sh [--go] 사이트폴더...   (--go 없으면 미리보기만)
# 예:     bash quarantine_webshells.sh sunwhadowatch.com
#         bash quarantine_webshells.sh --go sunwhadowatch.com
GO=0; [ "$1" = "--go" ] && { GO=1; shift; }
cd /home/chunks/web || exit 1
Q=/home/chunks/quarantine_$(date +%Y%m%d)
DECOY='^(index\.(php|html)|php\.ini|\.htaccess|admin\.php|about\.php|f[0-9a-z]*\.php|cmd\.php|tool\.php)$'
KEEP='^(plugins|uploads|upgrade|widgets)$'
[ $GO = 1 ] && { mkdir -p "$Q/files"; chmod 700 "$Q"; }
moved=0; skipped=0
for site in "$@"; do
  [ -d "$site" ] || { echo "no dir: $site"; continue; }
  # 가짜 .well-known (점 없는 well-known)
  cands=$(find "$site" -regextype posix-extended -type d \( -name well-known -o -regex '.*/[a-z0-9]{7}' \) | awk '{print length($0) " " $0}' | sort -n | cut -d' ' -f2-)
  while IFS= read -r d; do
    [ -d "$d" ] || continue
    b=$(basename "$d"); [[ "$b" =~ $KEEP ]] && continue
    bad=$(find "$d" -type f -printf '%f\n' 2>/dev/null | grep -Ev "$DECOY" | head -3)
    if [ -n "$bad" ]; then echo "SKIP(정상 파일 포함): $d  -> $(echo $bad | tr '\n' ' ')"; skipped=$((skipped+1)); continue; fi
    if [ $GO = 1 ]; then
      chmod -R u+w "$d" 2>/dev/null; chmod u+w "$(dirname "$d")" 2>/dev/null
      mkdir -p "$Q/files/$(dirname "$d")" && mv "$d" "$Q/files/$d" && moved=$((moved+1))
    else echo "MOVE: $d"; moved=$((moved+1)); fi
  done <<< "$cands"
done
echo "대상: $moved  건너뜀: $skipped  격리위치: $Q"
[ $GO = 0 ] && echo "미리보기입니다. 실제 이동은 --go 를 붙여 다시 실행하세요."
