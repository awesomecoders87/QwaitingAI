@echo off
echo Starting Reverb Server...
echo.
echo Note: This will run indefinitely. Press Ctrl+C to stop.
echo.
php -d max_execution_time=0 artisan reverb:start

