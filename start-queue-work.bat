@echo off
echo Starting Laravel Queue Worker...
echo Press Ctrl+C to stop the worker.

REM Navigate to your Laravel project directory
cd /d "D:\alvif\projects\app-pajak"

REM Start PHP artisan queue:work command
php artisan queue:work

timeout /t 2 /nobreak > nul
