@echo off
cd /d F:\UGM\Tugas\PRPL\bukuLembaranDanBeritaDesa\backend
powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "& 'C:\xampp\php\php.exe' artisan schedule:run 2>&1 | ForEach-Object { if ($_ -ne $null -and $_ -ne '') { '['+(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')+'] '+$_ } } | Add-Content 'storage\logs\schedule_runner.log'"
