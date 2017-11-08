<?php
include 'init.php';

echo $q = "INSERT INTO tests (TEST) VALUES ('".date('H:i:s', time())."')";
mysql_query($q);

?>