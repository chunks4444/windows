# 평목 DB 접속용 SSH 터널을 실행한다 (Windows). MySQL은 컨테이너 안에서만 열려있고
# 외부 TCP(6836)는 막혀 있으므로, 로컬 PHP 개발 서버(src/lib/db.php)가 127.0.0.1:13306으로
# 접속할 수 있도록 이 터널이 항상 떠 있어야 한다.
#
# 로그인 시 자동 실행되도록 register_task.ps1로 예약 작업에 등록해서 씀.
# 수동 실행: powershell -ExecutionPolicy Bypass -File start_tunnel.ps1

$SshExe = "$env:WINDIR\System32\OpenSSH\ssh.exe"
if (-not (Test-Path $SshExe)) { $SshExe = "ssh" }
$KeyPath = "$HOME\.ssh\pyeongmok_studio"

& $SshExe -N `
    -o ServerAliveInterval=30 `
    -o ServerAliveCountMax=3 `
    -o ExitOnForwardFailure=yes `
    -o StrictHostKeyChecking=accept-new `
    -p 6822 `
    -i $KeyPath `
    -L 13306:127.0.0.1:3306 `
    chunks@211.35.72.68
