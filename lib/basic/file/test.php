<?php
/*
include "DirectoryUtil.php";

$r = DirectoryUtil::getFilesWithExtension("./","php");
print_r($r);
*/

include "PropertiesFile.php";

$p = new PropertiesFile("../../../bin/security.conf");

print_r($p);

?>