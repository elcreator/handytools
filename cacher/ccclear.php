<?php
$dir = "./cache"; 
$cnt = 0;
 if($handle = opendir($dir)) 
 { 
     while($file = readdir($handle)) 
     { 
         if(is_file($dir.'/'.$file)){
             unlink($dir.'/'.$file);
	     $cnt++;
	 } 
     } 
     closedir($handle);
 }
die('Cache cleared. Removed ' . strval($cnt) . ' files.');
