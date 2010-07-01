color 1f
echo off
cls
echo ---------------
echo cogePHP
echo ---------------
rmdir /s /q ..\model
mkdir ..\model
php cogephp.php
php cogephpservice.php
pause
