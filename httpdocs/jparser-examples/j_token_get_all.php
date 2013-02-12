<?php
/**
 * jTokenizer example
 * Very simple - just dump out some tokenized source.
 * 
 * We don't need the full parser for this, we justinclude the tokenizer library
 */
require '../jparser-libs/jtokenizer.php';

/**
 * Get some example source code from a *.js file
 */
$source = file_get_contents('simple.js');

/**
 * Get the tokens as an array, just like the php tokenizer token_get_all function
 */
$tokens = j_token_get_all( $source );



echo '<?xml version="1.0" encoding="UTF-8" ?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>jTokenizer simple example :: j_token_get_all</title>
</head>
<body>

<pre><?php
// Dump out the tokens:
echo htmlentities( print_r($tokens,1), ENT_COMPAT, 'UTF-8' ); 
?></pre>

</body>
</html>