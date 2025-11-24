@echo off
REM ===========================
REM Database Setup Script
REM ===========================
REM This script recreates the database from scratch using seed files
REM Seed files are executed in order based on their numeric prefix (e.g., 0010_, 0020_)

setlocal enabledelayedexpansion

echo ========================================
echo Database Setup Script
echo ========================================
echo.

REM Set database path
set DB_PATH=database\app.db

REM Check if database exists and delete it
if exist %DB_PATH% (
    echo Deleting existing database...
    del %DB_PATH%
    echo Database deleted.
    echo.
)

echo Creating new database...
echo.

REM Count total seed files
set /a TOTAL=0
for %%f in (database\seed\*.sql) do set /a TOTAL+=1

if %TOTAL% EQU 0 (
    echo ERROR: No seed files found in database\seed\
    pause
    exit /b 1
)

echo Found %TOTAL% seed file(s) to execute.
echo.

REM Execute seed files in sequence (sorted by filename)
set /a COUNT=0
for /f "tokens=*" %%f in ('dir /b /o:n database\seed\*.sql 2^>nul') do (
    set /a COUNT+=1
    echo [!COUNT!/%TOTAL%] Executing %%f...
    sqlite3 %DB_PATH% < "database\seed\%%f"
    if !ERRORLEVEL! NEQ 0 (
        echo ERROR: Failed to execute %%f
        pause
        exit /b 1
    )
    echo %%f executed successfully.
    echo.
)

echo ========================================
echo Database setup completed successfully!
echo ========================================
echo.
echo Database location: %DB_PATH%
echo Executed %COUNT% seed file(s).
echo.

REM Verify database - count tables and show them
echo Verifying database...
echo.

echo Tables created:
sqlite3 %DB_PATH% ".tables"
echo.

REM Count tables
for /f %%a in ('sqlite3 %DB_PATH% "SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%%';"') do set TABLE_COUNT=%%a
echo Total tables: %TABLE_COUNT%
echo.

REM Show row counts for each table
echo Row counts per table:
echo ----------------------------------------
for /f "tokens=*" %%t in ('sqlite3 %DB_PATH% "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%%' ORDER BY name;"') do (
    for /f %%c in ('sqlite3 %DB_PATH% "SELECT COUNT(*) FROM %%t;"') do (
        echo   %%t: %%c rows
    )
)
echo ----------------------------------------
echo.

echo Setup complete!
pause