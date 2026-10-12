@echo off

cd /d "E:\xampp\htdocs\Laravel_Intern\restaurant-management-system-4deade04949b5628990c9b6566092bf648abd1b7\rms-api"

"E:\xampp\php\php.exe" artisan subscriptions:send-expiry-reminders

pause