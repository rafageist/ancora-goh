color 1f
echo off
cls
echo ---------------
echo cogePHP
echo ---------------
del ..\view\combo\*.* /s /q
del ..\view\gallery\*.* /s /q
del ..\view\grid\*.* /s /q
del ..\control\view\gallery\*.* /s /q
del ..\control\view\combo\*.* /s /q
del ..\control\data\add\*.* /s /q
del ..\control\data\set\*.* /s /q
del ..\control\data\del\*.* /s /q
php cogephpface.php
pause
