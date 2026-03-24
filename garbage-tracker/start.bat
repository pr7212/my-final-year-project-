@echo off
cd /d "C:\xampp\php"
php.exe -S localhost:8000 -t .
echo Server running at http://localhost:8000
pause
