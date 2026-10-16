@echo off
chcp 65001 > nul
title Kayan ERP - Local Installation Script

echo ========================================================
echo        تثبيت وتهيئة نظام كايان ERP للإنتاج المحلي
echo ========================================================
echo.

:: 1. التحقق من وجود PHP
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [خطأ]: PHP غير مثبت أو غير مضاف لمتغيرات البيئة (PATH).
    echo يرجى تثبيت XAMPP والتأكد من إضافة C:\xampp\php إلى PATH.
    pause
    exit /b 1
)

:: 2. التحقق من وجود Composer
composer -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [خطأ]: Composer غير مثبت على هذا الجهاز.
    echo يرجى تثبيت Composer من https://getcomposer.org
    pause
    exit /b 1
)

:: 3. نسخ ملف .env إذا لم يكن موجوداً
if not exist "..\..\.env" (
    if exist ".env" (
        echo [معلومات]: ملف .env موجود بالفعل.
    ) else (
        echo [إجراء]: جاري إنشاء ملف .env من النموذج الأساسي...
        copy ..\..\.env.example ..\..\.env > nul
    )
)

cd ..\..

echo [1/6] جاري تثبيت حزم PHP المحسنة للإنتاج...
call composer install --no-dev --optimize-autoloader --quiet

echo [2/6] جاري تثبيت وبناء أصول الواجهة والتصميم...
call npm install --silent
call npm run build

echo [3/6] جاري توليد مفتاح التشفير...
call php artisan key:generate --force

echo [4/6] جاري ربط وسائط التخزين (الشعار والملفات)...
call php artisan storage:link

echo [5/6] جاري ترحيل جداول قاعدة البيانات...
call php artisan migrate --force

echo [6/6] جاري تفعيل كاش الأداء والإنتاج (Config, Routes, Views)...
call php artisan optimize

echo.
echo ========================================================
echo   تم اكتمال تثبيت وتهيئة نظام كايان ERP بنجاح تام!
echo ========================================================
echo.
pause
