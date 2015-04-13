<?php

$dir = getcwd();

echo $dir . '<br>';

$dirLen = strlen($dir);

$rdir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), TRUE);

$i = 0;

foreach ($rdir as $file) {
    if ($file == '.' || $file == '..')
        continue;

    $chunks = explode('.', $file);
    $ext = strtolower(end($chunks));

 // uncomment ONE of these lines:
 //   if (!in_array($ext, array('html', 'shtml')))
 //   if (!in_array($ext, array('gif', 'jpeg', 'png', 'jpg')))
 //   if (!in_array($ext, array('pdf', 'doc', 'docx', 'zip', 'rar', 'exe', 'msi')))

        continue;
    
    $file = str_replace('\\', '/', $file);

	echo 'http:/' . substr($file, $dirLen) . '<br>';
    $i++;
}
echo $i . '<br>';
