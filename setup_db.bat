@echo off
REM ===========================
REM Database Setup Script
REM ===========================
REM This script recreates the database from scratch using seed files

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

REM Execute seed files in sequence
echo Executing seed files...
echo.

echo [1/6] Creating users table and seed data...
sqlite3 %DB_PATH% < database\seed\0010_users.sql
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to execute 0010_users.sql
    pause
    exit /b 1
)
echo Users table created successfully.
echo.

echo [2/6] Creating organizations table and seed data...
sqlite3 %DB_PATH% < database\seed\0020_organizations.sql
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to execute 0020_organizations.sql
    pause
    exit /b 1
)
echo Organizations table created successfully.
echo.

echo [3/6] Creating organization_departments table and seed data...
sqlite3 %DB_PATH% < database\seed\0030_organization_departments.sql
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to execute 0030_organization_departments.sql
    pause
    exit /b 1
)
echo Organization departments table created successfully.
echo.

echo [4/6] Creating auth_sessions table...
sqlite3 %DB_PATH% < database\seed\0040_auth_sessions.sql
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to execute 0040_auth_sessions.sql
    pause
    exit /b 1
)
echo Auth sessions table created successfully.
echo.

echo [5/6] Creating facility_teams table...
sqlite3 %DB_PATH% < database\seed\0050_facility_teams.sql
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to execute 0050_facility_teams.sql
    pause
    exit /b 1
)
echo Facility teams table created successfully.
echo.

echo [6/6] Creating organization_branches table...
sqlite3 %DB_PATH% < database\seed\0060_organization_branches.sql
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to execute 0060_organization_branches.sql
    pause
    exit /b 1
)
echo Organization branches table created successfully.
echo.

echo ========================================
echo Database setup completed successfully!
echo ========================================
echo.
echo Database location: %DB_PATH%
echo.

REM Display table count
echo Verifying tables...
sqlite3 %DB_PATH% ".tables"
echo.

echo Setup complete!
pause