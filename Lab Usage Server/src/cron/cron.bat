@echo off
cd /d %~dp0

start "" /B "C:\php-5.4.3\php.exe" "cron.php"

exit 0