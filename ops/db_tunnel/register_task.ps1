# Windows 로그인 시 DB SSH 터널이 자동으로 뜨도록 예약 작업에 등록한다. 1회 실행.
#   powershell -ExecutionPolicy Bypass -File register_task.ps1
#
# 터널이 죽어도(네트워크 끊김 등) 자동 재시작하도록 RestartCount/RestartInterval을 걸어둔다.

$TaskName   = "Pyeongmok-DBTunnel"
$ScriptPath = Join-Path $PSScriptRoot "start_tunnel.ps1"

$Action  = New-ScheduledTaskAction -Execute "powershell.exe" `
    -Argument "-NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$ScriptPath`""
$Trigger = New-ScheduledTaskTrigger -AtLogOn
$Settings = New-ScheduledTaskSettingsSet -ExecutionTimeLimit ([TimeSpan]::Zero) `
    -RestartCount 999 -RestartInterval (New-TimeSpan -Minutes 1) -StartWhenAvailable

Register-ScheduledTask -TaskName $TaskName -Action $Action -Trigger $Trigger `
    -Settings $Settings -Description "평목 DB 접속용 SSH 터널 (로그인 시 자동 시작, 로컬 개발용)" -Force

Write-Output "등록 완료: '$TaskName' 로그인 시 자동 실행"
Write-Output "지금 바로 시작하려면: Start-ScheduledTask -TaskName '$TaskName'"
