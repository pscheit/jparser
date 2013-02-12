<?php
/**
 * jParser example
 * 
 */
require '../jparser-libs/jparser.php';

/**
 * Get some example source code from a *.js file
 */
$source = file_get_contents('complex.js');

/**
 * Get the full parse tree
 */
$Prog = JParser::parse_string( $source );
if( ! $Prog instanceof JProgramNode ){
	die('Root of parse tree is not a JProgramNode');
}

// Collapsing back to a string minifies by default, because whitespace and comments are not in the tree
$min = $Prog->__toString();

// Obfuascate tree
$protect = array();
$Prog->obfuscate( $protect );
$obf = $Prog->__toString();


echo '<?xml version="1.0" encoding="UTF-8" ?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>jParser example :: j_obfuscate</title>
</head>
<body>

<h2>Minified code</h2>
<div style="font: 10pt monospace">
<?php
echo preg_replace('/(\r\n|\n|\r)/','<br />',htmlentities( $min, ENT_COMPAT, 'UTF-8') );
?>
</div>


<h2>Obfuscated code</h2>
<div style="font: 10pt monospace">
<?php
echo preg_replace('/(\r\n|\n|\r)/','<br />',htmlentities( $obf, ENT_COMPAT, 'UTF-8') );
?>
</div>

</body>
</html>