@echo off
chcp 65001 > nul
title تثبيت مخزن الخرداوات
cd /d "%~dp0"

echo.
echo ===================================
echo   تثبيت مخزن الخرداوات
echo   يرجى الانتظار...
echo ===================================
echo.

call :findphp
if "%PHP%"=="" goto nophp
echo [OK] PHP: %PHP%

if not exist ".env" copy ".env.example" ".env" > nul
echo [OK] تم إنشاء ملف الإعدادات

"%PHP%" artisan key:generate --force
echo [OK] تم إنشاء مفتاح التشفير

if not exist "database\database.sqlite" type nul > "database\database.sqlite"
echo [OK] تم إنشاء قاعدة البيانات

"%PHP%" artisan migrate --force
echo [OK] تم إعداد قاعدة البيانات

if not exist "storage\logs" mkdir "storage\logs"
if not exist "storage\framework\cache" mkdir "storage\framework\cache"
if not exist "storage\framework\sessions" mkdir "storage\framework\sessions"
if not exist "storage\framework\views" mkdir "storage\framework\views"
if not exist "bootstrap\cache" mkdir "bootstrap\cache"

echo.
echo ===================================
echo   تم التثبيت بنجاح!
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
