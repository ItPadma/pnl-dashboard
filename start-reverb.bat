@echo off
echo Starting Laravel Reverb...
echo Press Ctrl+C to stop the reverb.

REM Navigate to your Laravel project directory
cd /d "D:\alvif\projects\app-pajak"

REM Start PHP artisan reverb command
php artisan reverb:start

timeout /t 2 /nobreak > nul
