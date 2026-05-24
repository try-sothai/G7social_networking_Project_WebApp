@echo off
for /f %%i in ('powershell -command "Get-Date -format ddMMyyHHmm"') do set dt=%%i
if not exist "D:\backup\g7social" mkdir "D:\backup\g7social"
cd /d C:\wamp64\bin\mysql\mysql8.4.7\bin
mysqldump.exe -u root g7social_networking > "D:\backup\g7social\g7social_%dt%.sql"
echo Backup Done! g7social_%dt%.sql
pause