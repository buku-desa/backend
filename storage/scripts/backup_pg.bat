@echo off
setlocal enableextensions enabledelayedexpansion

REM ====== CONFIG (sesuaikan) ======
set "DB_HOST=127.0.0.1"
set "DB_PORT=5432"
set "DB_NAME="
set "DB_USER="
set "DB_PASSWORD="

REM Folder backup lokal, SAMAIN PUNYA KALIAN
set "BACKUP_DIR=F:\backups\myapp" 
REM Kompresi 0..9 (untuk -Z jika format custom)
set "COMPRESS_LEVEL=9"

REM Path PG bin
set "PG_BIN=C:\Program Files\PostgreSQL\16\bin"

if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

REM --- Timestamp aman lintas locale (yyyyMMdd-HHmmss) ---
for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd-HHmmss"') do set "ts=%%i"

REM --- Nama file ---
set "host_clean=%DB_HOST::=_%"
set "host_clean=%host_clean:/=_%"
set "BASENAME=pg_%DB_NAME%_%host_clean%_%ts%"
set "DUMP_FILE=%BACKUP_DIR%\%BASENAME%.dump"
set "GLOBALS_FILE=%BACKUP_DIR%\globals_%host_clean%_%ts%.sql"

REM ==== AUTH ====
REM Jika DB_PASSWORD terisi -> gunakan PGPASSWORD (bisa string kosong)
REM Jika DB_PASSWORD kosong -> pakai -w untuk cegah prompt password
set "PGPASSFLAG="
if defined DB_PASSWORD (
  set "PGPASSWORD=%DB_PASSWORD%"
) else (
  set "PGPASSWORD="
  set "PGPASSFLAG=-w"
)

REM --- helper: fungsi untuk memberi timestamp per baris ke backup.log ---
:RUN_TS
REM pemakaian: call :RUN_TS "<COMMAND STRING>"
set "CMD=%~1"
cmd /c %CMD% 2^>^&1 ^
| powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "$input | ForEach-Object { if ($_ -ne $null -and $_ -ne '') { '['+(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')+'] '+$_ } } | Add-Content -Path '%BACKUP_DIR%\backup.log'"
exit /b %ERRORLEVEL%

REM ---------- LOG START ----------
for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format yyyy-MM-dd HH:mm:ss"') do set "nowts=%%i"
echo [%nowts%] START backup %DB_NAME% >> "%BACKUP_DIR%\backup.log"

REM === Dump database format custom (-Fc) dengan timestamp per baris ===
call :RUN_TS "\"%PG_BIN%\pg_dump.exe\" -h \"%DB_HOST%\" -p \"%DB_PORT%\" -U \"%DB_USER%\" -d \"%DB_NAME%\" -Fc -Z %COMPRESS_LEVEL% --no-owner --no-privileges --verbose %PGPASSFLAG% -f \"%DUMP_FILE%\""
IF ERRORLEVEL 1 (
  for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format yyyy-MM-dd HH:mm:ss"') do set "nowerr=%%i"
  echo [%nowerr%] ERROR: pg_dump gagal >> "%BACKUP_DIR%\backup.log"
  goto :end
)

REM === Dump globals (roles, grants) dengan timestamp per baris ===
call :RUN_TS "\"%PG_BIN%\pg_dumpall.exe\" -h \"%DB_HOST%\" -p \"%DB_PORT%\" -U \"%DB_USER%\" --globals-only %PGPASSFLAG% 1^> \"%GLOBALS_FILE%\""

IF ERRORLEVEL 1 (
  for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format yyyy-MM-dd HH:mm:ss"') do set "nowwarn=%%i"
  echo [%nowwarn%] WARNING: pg_dumpall globals gagal >> "%BACKUP_DIR%\backup.log"
) ELSE (
  powershell -NoProfile -ExecutionPolicy Bypass -Command "Compress-Archive -Path '%GLOBALS_FILE%' -DestinationPath '%GLOBALS_FILE%.zip' -Force"
  del /q "%GLOBALS_FILE%" 2>nul
)

REM === Retensi sederhana (hapus yang > N hari) ===
set "RETENTION_DAYS=7"
forfiles /p "%BACKUP_DIR%" /m "pg_%DB_NAME%_%host_clean%_*.dump" /d -%RETENTION_DAYS% /c "cmd /c del @path"
forfiles /p "%BACKUP_DIR%" /m "globals_%host_clean%_*.sql.zip" /d -%RETENTION_DAYS% /c "cmd /c del @path"

for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format yyyy-MM-dd HH:mm:ss"') do set "nowts2=%%i"
echo [%nowts2%] FINISH backup %DB_NAME% >> "%BACKUP_DIR%\backup.log"

:end
endlocal
