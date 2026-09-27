# 평목 uploads/ 백업 설정 템플릿.
# 이 파일을 config.ps1 로 복사한 뒤 실제 값을 채워넣을 것 (config.ps1은 git-ignored).
#
#   Copy-Item config.example.ps1 config.ps1
#
# 서버가 publickey 인증만 허용하므로 비밀번호 대신 개인키 파일 경로를 지정한다
# (ops/db_tunnel, ssh pyeongmok 단축 설정과 동일한 키를 재사용).

$SshHost         = "211.35.72.68"
$SshPort         = 6822
$SshUser         = "chunks"
$IdentityFile    = "$env:USERPROFILE\.ssh\pyeongmok_studio"
$RemotePath      = "/home/chunks/web/studio.pyeongmok.com/uploads"
$LocalBackupRoot = Join-Path $PSScriptRoot "snapshots"
$RetentionDays   = 14
