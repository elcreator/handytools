<?php
/*
(C) Kirilyuk Artur, 2012
*/

// ===========================

$devPath = '/var/www/vhosts/development/htdocs/';
$prodPath = '/var/www/vhosts/production/htdocs/';

$devJSdir = $devPath . 'css';
$devCSSdir = $devPath . 'js';

$prodCSSBundlePrefix = 'style';
$prodJSBundlePrefix = 'common';

$devINIpath = $devPath . 'config/config.ini';
$prodINIpath = $prodPath . 'config/config.ini';

// ===========================

$devINI = parse_ini_file($devINIpath);

$CSS = array (
	'vkindex.css', 'jquery-ui-1.8.16.custom.css'
);
$JS = array (
	'plugins.js', 'jquery.jplayer.min.js', 'jquery-ui-1.8.16.custom.min.js', 'jquery.vk.js', 'main.js'
);
$tmpDir = './tmp';
$prodDir = './../api';
$ourTmpUrl = 'http://something.server/tmp';
$buildName = $_SERVER['appVersion'];

$cssInput = '/input.css';
$cssOutput = '/' . $buildName . '.css';
$cssMinifierUrl = 'http://reducisaurus.appspot.com/css?url=';

$jsInput = '/input.js';
$jsOutput = '/' . $buildName . '.js';
$jsMinifierUrl = 'http://closure-compiler.appspot.com/compile';
$jsMinifierParams = 'output_format=json&output_info=compiled_code&output_info=warnings&output_info=errors'
			. '&output_info=statistics&compilation_level=SIMPLE_OPTIMIZATIONS&warning_level=verbose'
			. '&code_url=' . urlencode($ourTmpUrl . $jsInput);
// ===========================


echo 'Starting build ' . $buildName . '...<br>';

if ($dh = opendir($tmpDir)){
    while(($file = readdir($dh))!== false){
	if (is_file($tmpDir . '/' . $file))
	    unlink($tmpDir . '/' . $file);
    }
    closedir($dh);
} else echo 'Cannot remove files...';


copy ('.htaccess', 'htaccess.txt');
unlink ('.htaccess');



// ----------
echo 'Optimizing CSS...<br>';

for ($i = 0; $i < count($CSS); $i++) {

    $cssbundle .= file_get_contents($CSS[$i]) . PHP_EOL; 

}

file_put_contents ($tmpDir . $cssInput, $cssbundle);

$minifiedCss = file_get_contents ($cssMinifierUrl . $ourTmpUrl . $cssInput);

file_put_contents ($tmpDir . $cssOutput, $minifiedCss);

$f = fopen ( $tmpDir . $cssOutput . '.gz', 'w' );
fwrite ( $f, gzencode ( $minifiedCss, 9 ) );
fclose ( $f );

echo 'Done.<br>';
// ----------


// ----------
echo 'Optimizing JS...<br>';

include_once('./s_http.php');

for ($i = 0; $i < count($JS); $i++) {

    $jsbundle .= file_get_contents($JS[$i]) . PHP_EOL; 

}

file_put_contents ($tmpDir . $jsInput, $jsbundle);

$shttp = new s_http();
$shttp->init();

$shttp->post($jsMinifierUrl, $jsMinifierParams);
$minJsJson = $shttp->data();
$minJsArr = json_decode($minJsJson, true);

$minifiedJs = $minJsArr['compiledCode'];

file_put_contents ($tmpDir . $jsOutput, $minifiedJs);

$f = fopen ( $tmpDir . $jsOutput . '.gz', 'w' );
fwrite ( $f, gzencode ( $minifiedJs, 9 ) );
fclose ( $f );

echo 'Done.<br>';
// ----------


// ----------
echo 'Deploying...<br>';
copy ($tmpDir . $jsOutput, $prodDir . $jsOutput);
copy ($tmpDir . $cssOutput, $prodDir . $cssOutput);
copy ($tmpDir . $jsOutput . '.gz', $prodDir . $jsOutput . '.gz');
copy ($tmpDir . $cssOutput . '.gz', $prodDir . $cssOutput . '.gz');
echo 'Done.<br>';
// ----------

copy ('htaccess.txt', '.htaccess');
unlink ('htaccess.txt');
