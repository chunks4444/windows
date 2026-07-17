# 평목 DB(windowspyeongmok)를 로컬로 백업한다. DB는 외부에 직접 노출되지 않으므로
# (MySQL bind-address 127.0.0.1, src/lib/db.php 참고) 이 스크립트가 SSH 터널을 직접
# 띄워서 mysqldump가 127.0.0.1:13306으로 접속하게 한 뒤, 끝나면 터널을 정리한다.
# uploads/ 파일 백업(ops/backup_uploads)과는 별도로 실행되는 스크립트.
#
# 실행 전:
#   1) config.example.ps1 을 config.ps1 로 복사
#   2) .my.cnf.example 을 .my.cnf 로 복사하고 비밀번호 채워넣기
#
# Windows 예약 작업 등록은 register_task.ps1 참고.

$ErrorActionPreference = "Stop"

$ScriptDir  = $PSScriptRoot
$ConfigPath = Join-Path $ScriptDir "config.ps1"

if (-not (Test-Path $ConfigPath)) {
    Write-Error "설정 파일 없음: $ConfigPath (config.example.ps1을 복사해서 만들 것)"
    exit 1
}

. $ConfigPath

if (-not (Test-Path $MyCnfPath)) {
    Write-Error "자격증명 파일 없음: $MyCnfPath (.my.cnf.example을 복사해서 만들 것)"
    exit 1
}

if (-not (Test-Path $MysqldumpExe)) {
    Write-Error "mysqldump.exe를 찾을 수 없습니다: $MysqldumpExe"
    exit 1
}

$LogFile = Join-Path $ScriptDir "backup_db_local.log"
function Write-Log($msg) {
    $line = "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] $msg"
    Write-Output $line
    Add-Content -Path $LogFile -Value $line
}

New-Item -ItemType Directory -Force -Path $LocalBackupRoot | Out-Null

$Timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$SqlFile   = Join-Path $LocalBackupRoot "${DbName}_${Timestamp}.sql"
$ZipFile   = "$SqlFile.zip"

# --- SSH 터널 시작 ---
Write-Log "DB 터널 시작 중..."
$tunnelArgs = @(
    "-N", "-o", "ServerAliveInterval=30", "-o", "ExitOnForwardFailure=yes",
    "-o", "StrictHostKeyChecking=accept-new",
    "-p", $SshPort, "-i", $SshKeyPath,
    "-L", "${TunnelLocalPort}:127.0.0.1:3306",
    "$SshUser@$SshHost"
)
$tunnelProc = Start-Process -FilePath $SshExe -ArgumentList $tunnelArgs -WindowStyle Hidden -PassThru

try {
    $tunnelUp = $false
    for ($i = 0; $i -lt 15; $i++) {
        Start-Sleep -Seconds 1
        try {
            $client = New-Object System.Net.Sockets.TcpClient
            $client.Connect("127.0.0.1", $TunnelLocalPort)
            $client.Close()
            $tunnelUp = $true
            break
        } catch { }
    }
    if (-not $tunnelUp) {
        Write-Log "실패 - DB 터널이 뜨지 않음"
        exit 1
    }
    Write-Log "DB 터널 연결됨 -> 백업 시작 -> $ZipFile"

    $dumpArgs = @(
        "--defaults-extra-file=$MyCnfPath",
        "--single-transaction",
        "--quick",
        "--routines",
        "--triggers",
        "--hex-blob",
        "--no-tablespaces",
        $DbName
    )

    & $MysqldumpExe @dumpArgs | Out-File -FilePath $SqlFile -Encoding utf8
    $exitCode = $LASTEXITCODE

    if ($exitCode -ne 0 -or -not (Test-Path $SqlFile) -or (Get-Item $SqlFile).Length -eq 0) {
        Write-Log "실패 (exit $exitCode) - 불완전한 덤프 삭제: $SqlFile"
        Remove-Item -Force $SqlFile -ErrorAction SilentlyContinue
        exit 1
    }

    Compress-Archive -Path $SqlFile -DestinationPath $ZipFile -Force
    Remove-Item -Force $SqlFile
} finally {
    Stop-Process -Id $tunnelProc.Id -Force -ErrorAction SilentlyContinue
}

$sizeMB = [math]::Round((Get-Item $ZipFile).Length / 1MB, 2)
Write-Log "백업 완료: $ZipFile (${sizeMB}MB)"

# 보존기간 지난 백업 정리
Get-ChildItem -Path $LocalBackupRoot -Filter "${DbName}_*.sql.zip" | Where-Object {
    $_.CreationTime -lt (Get-Date).AddDays(-$RetentionDays)
} | ForEach-Object {
    Write-Log "오래된 백업 삭제: $($_.Name)"
    Remove-Item -Force $_.FullName
}

Write-Log "완료"
