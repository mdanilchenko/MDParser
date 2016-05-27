<?php
/**
 * User: maksimdanilchenko
 * Date: 25.05.16
 * Time: 20:59
 */
require 'MDParser.php';
$filename_prev = 'Chatvdvoem Socket Server API.md';//prev. revision filename
$filename = 'Chatvdvoem Socket Server API2.md'; // current revision filename
$output_name = str_replace('.md','.html' ,$filename );

//$file_prev = file_get_contents('documents/test_prev.txt');
$file_prev = file_get_contents('documents/'.$filename_prev);
$file = file_get_contents('documents/'.$filename);

$parser = new MDParser();
//$out = $parser->parseToHtml($file); //create html for currnt version

$out = $parser->parseVersionCompare($file_prev,$file,2); //create compared html
file_put_contents('outputs/'.$output_name,$out);

?>