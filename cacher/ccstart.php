<?php
$ccsTimeStart = microtime(true);
$ccsFileName = 'cache/'.md5($_SERVER['REQUEST_URI']).'.html';
$ccsIsCached = false;
$ccsTime = 604800; // Время кеша в секундах
 
if (file_exists($ccsFileName)) {
  if ((time() - filemtime($ccsFileName)) < $ccsTime) {
    $ccsIsCached = true;
  } else {
    unlink($ccsFileName);
    $ccsIsCached = false;
  }
}
 
if ($ccsIsCached) {
  readfile($ccsFileName);
  die ('<!-- cached. time: ' . strval(microtime(true) - $ccsTimeStart) . 'sec. -->');
}

ob_start();
