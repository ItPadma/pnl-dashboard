@echo off
echo Starting all Laravel services...

REM Start Reverb
START /MIN "Reverb" cmd /c "D:\alvif\projects\app-pajak\start-reverb.bat"

REM Start Queue Worker
START /MIN "QueueWorker" cmd /c "D:\alvif\projects\app-pajak\start-queue-work.bat"

REM Start Selenium Server
START /MIN "Selenium" cmd /c "D:\alvif\projects\app-pajak\start-selenium-server.bat"

echo All services have been started.
timeout /t 3 /nobreak > nul
