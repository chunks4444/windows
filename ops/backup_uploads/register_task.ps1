# Windows 예약 작업에 uploads 백업을 등록한다. 관리자 권한 PowerShell에서 1회 실행.
#   powershell -ExecutionPolicy Bypass -File register_task.ps1
#
# 이미 등록된 작업이 있으면 설정을 갱신한다. 실행 시각을 바꾸려면 $TriggerTime을 수정.

$TaskName    = "Pyeongmok-BackupUploads"
$ScriptPath  = Join-Path $PSScriptRoot "backup_uploads.ps1"
$TriggerTime = "00:00"

$Action  = New-ScheduledTaskAction -Execute "powershell.exe" `
    -Argument "-NoProfile -ExecutionPolicy Bypass -File `"$ScriptPath`""
$Trigger = New-ScheduledTaskTrigger -Daily -At $TriggerTime
$Settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -DontStopOnIdleEnd

Register-ScheduledTask -TaskName $TaskName -Action $Action -Trigger $Trigger `
    -Settings $Settings -Description "평목 서버 uploads/ 폴더 로컬 백업 (매일)" -Force

Write-Output "등록 완료: '$TaskName' 매일 $TriggerTime 실행"
Write-Output "수동 실행 테스트: Start-ScheduledTask -TaskName '$TaskName'"
