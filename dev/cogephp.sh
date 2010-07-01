#!/bin/sh
rm ../model/*.*
php ./cogephp.php
chmod --recursive 0777 ../model
chmod --recursive 0777 ../control

