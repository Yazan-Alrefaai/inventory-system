@echo off
chcp 65001 > nul
title مخزن الخرداوات
cd /d "%~dp0"

echo.
echo ===================================
echo   مخزن الخرداوات - جاري التشغيل
echo ===================================
echo.

call :findphp
if "%PHP%"=="" goto nophp

start /b cmd /c "timeout /t 2 > nul && start http://localhost:8000"

echo   البرنامج يعمل على: http://localhost:8000
echo   لإيقاف التشغيل: أغلق هذه النافذة
echo.
echo   لا تغلق هذه النافذة أثناء العمل!
echo ===================================
echo.

set PHP_INI=%~dp0php\php.ini
if exist "%PHP_INI%" (
    "%PHP%" -c "%PHP_INI%" artisan serve --port=8000
) else (
    "%PHP%" artisan serve --port=8000
)

pause
exit /b 0

:findphp
set PHP=
php -v > nul 2>&1
if not errorlevel 1 set PHP=php
if not "%PHP%"=="" exit /b 0
if exist "%~dp0php\php.exe" set PHP=%~dp0php\php.exe
if not "%PHP%"=="" exit /b 0
if exist "%~dp0..\php\php.exe" set PHP=%~dp0..\php\php.exe
if not "%PHP%"=="" exit /b 0
if exist "C:\php\php.exe" set PHP=C:\php\php.exe
if not "%PHP%"=="" exit /b 0
for /d %%D in ("C:\laragon\bin\php\php-*") do if exist "%%D\php.exe" set PHP=%%D\php.exe
exit /b 0

:nophp
echo [خطأ] لم يتم العثور على PHP على هذا الجهاز!
echo ضع مجلد php بجانب مجلد البرنامج او داخله ثم حاول مجدداً.
pause
exit /b 1
