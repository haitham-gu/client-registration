@echo off
echo ==========================================
echo WA Registration - Database Export Script
echo ==========================================
echo.

REM Set XAMPP paths
set XAMPP_PATH=C:\xampp
set MYSQL_PATH=%XAMPP_PATH%\mysql\bin
set PHP_PATH=%XAMPP_PATH%\php

echo Checking XAMPP installation...
if not exist "%MYSQL_PATH%\mysqldump.exe" (
    echo ERROR: MySQL not found at %MYSQL_PATH%
    echo Please update XAMPP_PATH variable in this script
    pause
    exit /b 1
)

echo.
echo Creating backup directory...
if not exist "backup" mkdir backup

echo.
echo Exporting database structure and data...
"%MYSQL_PATH%\mysqldump.exe" -u root --single-transaction --routines --triggers wa_registration > backup\wa_registration_export.sql

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ✓ Database exported successfully to: backup\wa_registration_export.sql
    echo.
    echo To import on your hosting provider:
    echo 1. Create a new database in your hosting control panel
    echo 2. Upload the backup\wa_registration_export.sql file
    echo 3. Run the SQL import in phpMyAdmin or similar tool
    echo.
) else (
    echo.
    echo ✗ Database export failed!
    echo Make sure MySQL is running in XAMPP Control Panel
    echo.
)

echo Generating production checklist...
echo.
echo ==========================================
echo PRODUCTION DEPLOYMENT CHECKLIST
echo ==========================================
echo.
echo [ ] 1. Database exported ✓
echo [ ] 2. Update config.php with hosting database details
echo [ ] 3. Change admin passwords (DONE ✓)
echo [ ] 4. Upload files to hosting provider
echo [ ] 5. Import database SQL file
echo [ ] 6. Enable SSL certificate
echo [ ] 7. Test registration and admin login
echo [ ] 8. Update .htaccess for HTTPS redirect
echo.
echo Current admin credentials:
echo Username: admin    Password: Admin@2025!
echo Username: manager  Password: Manager@2025!
echo.

pause
