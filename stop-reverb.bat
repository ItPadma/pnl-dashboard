@echo off
echo Stopping Laravel Reverb...

REM Find and kill the PHP process running reverb
taskkill /F /IM php.exe /FI "WINDOWTITLE eq *reverb:start*"

REM Check if the process was successfully terminated
if %errorlevel% equ 0 (
    echo Reverb has been successfully stopped.
) else (
    echo No running Reverb process found or could not be stopped.
)

timeout /t 2 /nobreak > nul
