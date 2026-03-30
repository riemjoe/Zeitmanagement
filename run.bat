@echo off
echo Starte Laravel Server auf Port 9913...
start "" http://localhost:9913
php artisan serve --port=9913
echo "Server wurde gestoppt."
pause >nul