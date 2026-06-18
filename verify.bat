@echo off
echo.
echo ======================================
echo Monitor Application - Structure Verification
echo ======================================
echo.

echo Checking essential directories...
for %%D in (app bootstrap config database public resources storage) do (
    if exist "%%D\" (
        echo   [OK] %%D/
    ) else (
        echo   [MISSING] %%D/
    )
)
echo.

echo Checking essential files...
for %%F in (.env composer.json bootstrap\app.php public\index.php config\database.php) do (
    if exist "%%F" (
        echo   [OK] %%F
    ) else (
        echo   [MISSING] %%F
    )
)
echo.

echo Checking migrations...
dir /B database\migrations\*.php >NUL 2>&1
if %ERRORLEVEL%==0 (
    echo   [OK] Migration files found
) else (
    echo   [MISSING] No migration files
)
echo.

echo Checking API endpoints...
for %%E in (public\api\servers\list.php public\api\settings\update.php public\api\reports\metrics.php) do (
    if exist "%%E" (
        echo   [OK] %%E
    ) else (
        echo   [MISSING] %%E
    )
)
echo.

echo Checking view templates...
for %%V in (resources\views\auth\login.php resources\views\servers\index.php resources\views\reports\index.php) do (
    if exist "%%V" (
        echo   [OK] %%V
    ) else (
        echo   [MISSING] %%V
    )
)
echo.

echo ======================================
echo Verification Complete
echo ======================================
echo.
echo To run the application:
echo 1. Ensure PDO database driver is installed
echo 2. Run: php database/migrate.php up
echo 3. Run: php database/seed.php
echo 4. Start server: php -S localhost:8000 -t public
echo 5. Access: http://localhost:8000/
echo.
pause
