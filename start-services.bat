@echo off
echo Starting all Laravel services...

REM Start Reverb
START /MIN D:\alvif\projects\app-pajak\start-reverb.bat

REM Start Queue Worker
START /MIN D:\alvif\projects\app-pajak\start-queue-work.bat

echo All services have been started.
timeout /t 3 /nobreak > nul
