# 평목 서버(studio.pyeongmok.com)의 uploads/ 폴더(실사용자 업로드 원본: wallpapers, svg_insert,
# svg_motifs, drawing_thumbs 등)를 로컬로 백업한다. DB에는 파일 경로만 저장되고 실제 파일은
# 서버 디스크에만 있으므로, DB 백업(ops/backup_db.sh)과 별도로 이 스크립트가 필요하다.
#
# 실행 전:
#   1) config.example.ps1 을 config.ps1 로 복사하고 값 확인 (개인키는 ssh pyeongmok과 동일한 것 재사용)
#
# Windows 예약 작업 등록은 register_task.ps1 참고.
# 서버가 2026-09-26 침해 대응으로 SSH 비밀번호 인증을 막고 publickey만 허용하게 되면서
# PuTTY(pscp+비밀번호) 방식에서 Windows 내장 OpenSSH(scp+키) 방식으로 전환함.

$ErrorActionPreference = "Stop"

$ScriptDir  = $PSScriptRoot
$ConfigPath = Join-Path $ScriptDir "config.ps1"

if (-not (Test-Path $ConfigPath)) {
    Write-Error "설정 파일 없음: $ConfigPath (config.example.ps1을 복사해서 만들 것)"
    exit 1
}

. $ConfigPath

if (-not (Test-Path $IdentityFile)) {
    Write-Error "개인키 파일 없음: $IdentityFile"
    exit 1
}

$Scp = "$env:WINDIR\System32\OpenSSH\scp.exe"
if (-not (Test-Path $Scp)) {
    Write-Error "scp.exe를 찾을 수 없습니다: $Scp"
    exit 1
}

$LogFile = Join-Path $ScriptDir "backup_uploads.log"
function Write-Log($msg) {
    $line = "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] $msg"
    Write-Output $line
    Add-Content -Path $LogFile -Value $line
}

New-Item -ItemType Directory -Force -Path $LocalBackupRoot | Out-Null

$Timestamp    = Get-Date -Format "yyyyMMdd_HHmmss"
$SnapshotDir  = Join-Path $LocalBackupRoot $Timestamp
New-Item -ItemType Directory -Force -Path $SnapshotDir | Out-Null

Write-Log "백업 시작 -> $SnapshotDir"

$scpArgs = @(
    "-P", $SshPort,
    "-i", $IdentityFile,
    "-o", "StrictHostKeyChecking=accept-new",
    "-r",
    "$SshUser@${SshHost}:$RemotePath",
    $SnapshotDir
)

& $Scp @scpArgs
$exitCode = $LASTEXITCODE

if ($exitCode -ne 0) {
    Write-Log "실패 (exit $exitCode) - 불완전한 스냅샷 삭제: $SnapshotDir"
    Remove-Item -Recurse -Force $SnapshotDir -ErrorAction SilentlyContinue
    exit $exitCode
}

$fileCount = (Get-ChildItem -Recurse -File -Path $SnapshotDir | Measure-Object).Count
$sizeMB    = [math]::Round((Get-ChildItem -Recurse -File -Path $SnapshotDir | Measure-Object -Property Length -Sum).Sum / 1MB, 1)
Write-Log "백업 완료: ${fileCount}개 파일, ${sizeMB}MB"

# 보존기간 지난 스냅샷 정리
Get-ChildItem -Path $LocalBackupRoot -Directory | Where-Object {
    $_.CreationTime -lt (Get-Date).AddDays(-$RetentionDays)
} | ForEach-Object {
    Write-Log "오래된 스냅샷 삭제: $($_.Name)"
    Remove-Item -Recurse -Force $_.FullName
}

Write-Log "완료"
