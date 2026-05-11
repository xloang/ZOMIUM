@echo off
title Git Auto Push

echo =========================
echo   GIT AUTO COMMIT TOOL
echo =========================

:: Değişiklikleri ekle
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

:: Push yap
echo.
echo [3] Github'a gonderiliyor...
git push

echo.
echo Islem tamamlandi.
pause