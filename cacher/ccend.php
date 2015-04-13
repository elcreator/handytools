<?php
$ccsText = ob_get_clean();
echo $ccsText;

$ccsFileHndl = fopen($ccsFileName, 'w+');
fwrite($ccsFileHndl, $ccsText);
fclose($ccsFileHndl);

die('<!-- uncached. time: ' . strval(microtime(true) - $ccsTimeStart) . 'sec. -->');
