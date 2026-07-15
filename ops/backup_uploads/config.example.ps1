# 평목 uploads/ 백업 설정 템플릿.
# 이 파일을 config.ps1 로 복사한 뒤 실제 값을 채워넣을 것 (config.ps1은 git-ignored).
#
#   Copy-Item config.example.ps1 config.ps1
#
# 비밀번호는 이 파일에 직접 적지 말고 .pw 파일에 따로 저장한다 (아래 $PwFile 참고).
#   Set-Content -Path .pw -Value '실제_비밀번호' -NoNewline

$SshHost         = "211.35.72.68"
$SshPort         = 6822
$SshUser         = "chunks"
$PwFile          = Join-Path $PSScriptRoot ".pw"
$RemotePath      = "/home/chunks/web/studio.pyeongmok.com/uploads"
$LocalBackupRoot = Join-Path $PSScriptRoot "snapshots"
$RetentionDays   = 14
