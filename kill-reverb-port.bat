@echo off
echo Checking for processes using port 8443...
echo.

netstat -ano | findstr :8443 >nul
if %errorlevel% neq 0 (
    echo No process found using port 8443.
    echo Port is available.
    pause
    exit /b
)

echo Found process(es) using port 8443:
netstat -ano | findstr :8443
echo.

for /f "tokens=5" %%a in ('netstat -ano ^| findstr :8443') do (
    set PID=%%a
    echo Killing process with PID: %%a
    taskkill /F /PID %%a >nul 2>&1
    if %errorlevel% equ 0 (
        echo Successfully killed process %%a
    ) else (
        echo Failed to kill process %%a (may require admin rights)
    )
)

echo.
echo Done! You can now start Reverb server.
pause
