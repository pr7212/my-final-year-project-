@echo off
set "APP_DIR=%~dp0"

where php >nul 2>nul
if errorlevel 1 (
  echo PHP was not found in PATH.
  echo Install PHP or add it to PATH, then run this script again.
  pause
  exit /b 1
)

php -S localhost:8000 -t "%APP_DIR%"
echo Server running at http://localhost:8000
pause
