@echo off
echo Starting Selenium Standalone server...
echo Press Ctrl+C to stop the server.

REM Navigate to your Laravel project directory
cd /d "D:\Dev\chromedriver-win64"

REM Start PHP artisan queue:work command
java -jar selenium-server-4.35.0.jar standalone

timeout /t 2 /nobreak > nul
