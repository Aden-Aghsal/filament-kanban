@echo off
cd /d "%~dp0\.."
echo Starting Laravel queue worker...
:loop
php artisan queue:work database --sleep=3 --tries=3 --timeout=120
echo Queue worker stopped. Restarting in 2 seconds...
timeout /t 2 /nobreak >nul
goto loop
