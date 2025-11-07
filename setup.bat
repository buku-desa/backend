@echo off
echo ====================================
echo Setup Backend Laravel - Buku Desa
echo ====================================
echo.

REM Check if .env exists
if not exist .env (
    echo [1/6] Copying .env.example to .env...
    copy .env.example .env
) else (
    echo [1/6] .env already exists, skipping...
)

echo [2/6] Generating application key...
php artisan key:generate

echo [3/6] Creating database file...
if not exist database\database.sqlite (
    type nul > database\database.sqlite
    echo Database file created.
) else (
    echo Database file already exists.
)

echo [4/6] Running migrations...
php artisan migrate --force

echo [5/6] Creating test users...
php artisan tinker --execute="try { App\Models\User::firstOrCreate(['username' => 'sekdes'], ['name' => 'Sekretaris Desa', 'email' => 'sekdes@test.com', 'password' => bcrypt('password'), 'role' => 'sekdes']); } catch (Exception $e) { echo 'User sekdes already exists'; }"
php artisan tinker --execute="try { App\Models\User::firstOrCreate(['username' => 'kepdes'], ['name' => 'Kepala Desa', 'email' => 'kepdes@test.com', 'password' => bcrypt('password'), 'role' => 'kepdes']); } catch (Exception $e) { echo 'User kepdes already exists'; }"

echo.
echo ====================================
echo Setup completed!
echo ====================================
echo.
echo You can now run the server with:
echo   php artisan serve
echo.
echo Login credentials:
echo   Sekdes - sekdes@test.com / password
echo   Kepdes - kepdes@test.com / password
echo.
pause
