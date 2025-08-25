@echo off
echo Stopping all Laravel services...

REM Stop Reverb
taskkill /F /FI "WINDOWTITLE eq Reverb*" /T

REM Stop Queue Worker
taskkill /F /FI "WINDOWTITLE eq QueueWorker*" /T

REM Stop Selenium Server
taskkill /F /FI "WINDOWTITLE eq Selenium*" /T

echo All services have been stopped.
timeout /t 3 /nobreak > nul
