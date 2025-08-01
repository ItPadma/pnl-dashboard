@echo off
echo Stopping Laravel Queue Worker...

REM Find and kill the PHP process running queue:work
taskkill /F /IM php.exe /FI "WINDOWTITLE eq *queue:work*"

REM Check if the process was successfully terminated
if %errorlevel% equ 0 (
    echo Queue Worker has been successfully stopped.
) else (
    echo No running Queue Worker process found or could not be stopped.
)

timeout /t 2 /nobreak > nul
