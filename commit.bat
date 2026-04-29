@echo off
title Git Auto Commit

echo ============================
echo   Git Auto Commit Script
echo ============================

:: Commit mesajını kullanıcıdan al
set /p msg=Commit mesajini gir:

:: Değişiklikleri ekle
git add .

:: Commit at
git commit -m "%msg%"

echo.
echo Commit tamamlandi!
pause
