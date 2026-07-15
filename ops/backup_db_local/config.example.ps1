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
