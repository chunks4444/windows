# 평목 DB(windowspyeongmok) 로컬 백업 설정 템플릿.
# 이 파일을 config.ps1 로 복사한 뒤 값 확인 (config.ps1은 git-ignored).
#
#   Copy-Item config.example.ps1 config.ps1
#
# 접속 자격증명은 이 파일이 아니라 .my.cnf 에 따로 저장한다 (.my.cnf.example 참고).

$MysqldumpExe    = "C:\xampp\mysql\bin\mysqldump.exe"
$MyCnfPath       = Join-Path $PSScriptRoot ".my.cnf"
$DbName          = "windowspyeongmok"
$LocalBackupRoot = "D:\web_backup\pyeongmok\db"
$RetentionDays   = 14

# DB는 외부에 직접 노출되지 않고(MySQL bind-address 127.0.0.1) SSH 터널로만 접근 가능하므로,
# mysqldump 실행 전에 이 스크립트가 직접 임시 터널을 띄웠다가 끝나면 정리한다.
$SshExe          = "$env:WINDIR\System32\OpenSSH\ssh.exe"
$SshKeyPath      = "$HOME\.ssh\pyeongmok_studio"
$SshHost         = "211.35.72.68"
$SshPort         = 6822
$SshUser         = "chunks"
$TunnelLocalPort = 13306
