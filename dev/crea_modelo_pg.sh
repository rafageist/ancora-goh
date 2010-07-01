#!/bin/sh
rm ./src/*.*
php ./crea_modelo_pg.php
chmod --recursive 0777 ./src