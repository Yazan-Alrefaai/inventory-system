@echo off
chcp 65001 > nul
title تحديث مخزن الخرداوات
cd /d "%~dp0"

echo.
echo ===================================
echo   تحديث مخزن الخرداوات
echo   يرجى الانتظار...
echo ===================================
echo.

call :findphp
if "%PHP%"=="" goto nophp
echo [OK] PHP: %PHP%

if not exist "database\database.sqlite" goto migrate

if not exist "database\backups" mkdir "database\backups"
set STAMP=unknown
for /f %%I in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd-HHmmss"') do set STAMP=%%I
copy "database\database.sqlite" "database\backups\backup-%STAMP%.sqlite" > nul
echo [OK] تم أخذ نسخة احتياطية من قاعدة البيانات

:migrate
set PHP_INI=%~dp0php\php.ini
if exist "%PHP_INI%" (
    "%PHP%" -c "%PHP_INI%" artisan migrate --force
) else (
    "%PHP%" artisan migrate --force
)
echo [OK] تم تحديث قاعدة البيانات

if exist "%PHP_INI%" (
    "%PHP%" -c "%PHP_INI%" artisan view:clear > nul 2>&1
    "%PHP%" -c "%PHP_INI%" artisan route:clear > nul 2>&1
    "%PHP%" -c "%PHP_INI%" artisan config:clear > nul 2>&1
) else (
    "%PHP%" artisan view:clear > nul 2>&1
    "%PHP%" artisan route:clear > nul 2>&1
    "%PHP%" artisan config:clear > nul 2>&1
)
echo [OK] تم تنظيف الملفات المؤقتة

echo.
echo ===================================
echo   تم التحديث بنجاح!
echo   الآن انقر على: تشغيل.bat
echo ===================================
echo.
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
