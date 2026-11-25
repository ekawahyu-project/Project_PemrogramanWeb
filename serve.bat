@echo off
:: AlpetBizz — Local Dev Server
:: Jalankan: serve.bat
:: Buka browser ke http://127.0.0.1:8000

set APP_KEY=base64:ljpaVJMq/klLWDkfQ1twnEcpLBxekRpZTRsycLAwWHY=
set APP_ENV=local
set APP_DEBUG=true

echo.
echo  AlpetBizz Dev Server
echo  http://127.0.0.1:8000
echo  Tekan Ctrl+C untuk berhenti
echo.

php artisan serve
