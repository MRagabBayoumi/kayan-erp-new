@echo off
setlocal enabledelayedexpansion
title Kayan ERP - Automated Setup and Installer

echo =======================================================================
echo                 KAYAN ERP - ONE-CLICK INSTALLER
echo =======================================================================
echo.

:: -------------------------------------------------------------------------
:: 1. Detect PHP
:: -------------------------------------------------------------------------
echo [1/8] Checking PHP environment...
set "PHP_CMD=php"
where php >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set "PATH=C:\xampp\php;!PATH!"
        set "PHP_CMD=C:\xampp\php\php.exe"
        echo       Found PHP in XAMPP (C:\xampp\php\php.exe)
    ) else if exist "C:\laragon\bin\php" (
        for /d %%D in (C:\laragon\bin\php\php-*) do (
            if exist "%%D\php.exe" (
                set "PATH=%%D;!PATH!"
                set "PHP_CMD=%%D\php.exe"
                echo       Found PHP in Laragon (%%D\php.exe)
            )
        )
    ) else (
        echo [ERROR] PHP is not found on your system!
        echo Please install XAMPP (PHP 8.2+) or add PHP to your Windows PATH.
        echo Download: https://www.apachefriends.org/
        echo.
        pause
        exit /b 1
    )
) else (
    echo       PHP is detected successfully.
)

:: -------------------------------------------------------------------------
:: 2. Detect Composer
:: -------------------------------------------------------------------------
echo [2/8] Checking Composer...
where composer >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\ProgramData\ComposerSetup\bin\composer.bat" (
        set "PATH=C:\ProgramData\ComposerSetup\bin;!PATH!"
        echo       Found Composer in C:\ProgramData\ComposerSetup\bin
    ) else (
        echo [ERROR] Composer is not installed or not in PATH!
        echo Please install Composer from: https://getcomposer.org/
        echo.
        pause
        exit /b 1
    )
) else (
    echo       Composer is detected successfully.
)

:: -------------------------------------------------------------------------
:: 3. Detect Node.js and NPM
:: -------------------------------------------------------------------------
echo [3/8] Checking Node.js and NPM...
where npm >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\Program Files\nodejs\npm.cmd" (
        set "PATH=C:\Program Files\nodejs;!PATH!"
        echo       Found Node.js in C:\Program Files\nodejs
    ) else (
        echo [ERROR] Node.js / NPM is not installed or not in PATH!
        echo Please install Node.js (LTS 18 or 20) from: https://nodejs.org/
        echo.
        pause
        exit /b 1
    )
) else (
    echo       Node.js / NPM is detected successfully.
)

:: -------------------------------------------------------------------------
:: 4. Setup .env file
:: -------------------------------------------------------------------------
echo [4/8] Configuring environment file (.env)...
if not exist ".env" (
    if exist ".env.example" (
        copy ".env.example" ".env" >nul
        echo       Created .env from .env.example
    ) else (
        echo [ERROR] Neither .env nor .env.example was found!
        pause
        exit /b 1
    )
) else (
    echo       .env file already exists.
)

:: -------------------------------------------------------------------------
:: 5. Auto-Create MySQL Database if MySQL is running
:: -------------------------------------------------------------------------
echo [5/8] Checking MySQL and creating database (kayan_erp)...
set "MYSQL_CMD=mysql"
where mysql >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\xampp\mysql\bin\mysql.exe" (
        set "MYSQL_CMD=C:\xampp\mysql\bin\mysql.exe"
    )
)

"%MYSQL_CMD%" -u root -e "CREATE DATABASE IF NOT EXISTS kayan_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" >nul 2>nul
if %errorlevel% equ 0 (
    echo       Database 'kayan_erp' verified/created in MySQL successfully.
) else (
    echo       [NOTE] Could not connect to MySQL root automatically.
    echo              Make sure MySQL service is started (e.g. from XAMPP Control Panel)
    echo              and database 'kayan_erp' exists.
)

:: -------------------------------------------------------------------------
:: 6. Install PHP & Node Dependencies
:: -------------------------------------------------------------------------
echo [6/8] Installing dependencies and building production assets...
echo       - Running composer install...
call composer install --no-interaction --prefer-dist --optimize-autoloader --quiet

echo       - Running npm install and build...
call npm install --silent
call npm run build

:: -------------------------------------------------------------------------
:: 7. Run Migrations, Seeds, and Application Keys
:: -------------------------------------------------------------------------
echo [7/8] Running database migrations and seeders...
call php artisan key:generate --force
call php artisan storage:link >nul 2>nul
call php artisan migrate --force
call php artisan db:seed --force

:: -------------------------------------------------------------------------
:: 8. Optimize Cache for Production
:: -------------------------------------------------------------------------
echo [8/8] Clearing and optimizing caches...
call php artisan optimize:clear >nul 2>nul
call php artisan optimize >nul 2>nul

echo.
echo =======================================================================
echo                     INSTALLATION COMPLETED!
echo =======================================================================
echo.
echo  Default System Credentials:
echo  -------------------------------------------------------------
echo  URL:      http://localhost:8000
echo  Email:    admin@kayan-erp.com
echo  Password: admin123456
echo.
echo  Local Network IP Address:
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /i "IPv4"') do (
    echo  LAN URL:  http:%%a:8000
)
echo =======================================================================
echo.

set /p START_NOW="Do you want to start the application server now? (Y/N): "
if /i "!START_NOW!"=="Y" (
    echo.
    echo Starting Kayan ERP Server on http://0.0.0.0:8000 ...
    echo Opening your default browser...
    start http://localhost:8000
    echo Press Ctrl+C to stop the server anytime.
    echo.
    php artisan serve --host=0.0.0.0 --port=8000
) else (
    echo.
    echo You can start the server anytime by running:
    echo   php artisan serve --host=0.0.0.0 --port=8000
    echo or using start-dev.bat
    echo.
    pause
)
