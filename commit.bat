@echo off
title Git Auto Push

echo =========================
echo   GIT AUTO COMMIT TOOL
echo =========================

:: Degisiklikleri ekle
echo.
echo [1] Degisiklikler ekleniyor...
git add .

:: Commit mesajı al
echo.
set /p msg=Commit mesajini gir: 

:: Commit at
echo.
echo [2] Commit atiliyor...
git commit -m "%msg%"

:: Remote degisiklikleri cek
echo.
echo [3] Github degisiklikleri aliniyor...
git pull --rebase origin main

:: Push yap
echo.
echo [4] Github'a gonderiliyor...
git push origin main

echo.
echo Islem tamamlandi.
pause