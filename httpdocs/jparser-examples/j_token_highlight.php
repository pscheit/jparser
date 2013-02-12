<?php
/**
 * jTokenizer example
 * More complex - show how the tokenizer can be used for highlighting source code
 * 
 * We don't need the full parser for this, we justinclude the tokenizer library
 */
require '../jparser-libs/jtokenizer.php';

/**
 * Get some juice source code from a *.js file
 */
$source = file_get_contents('complex.js');

/**
 * Get the tokens as an array, just like the php tokenizer token_get_all function
 */
$tokens = j_token_get_all( $source );

/**
 * Utility for printing out tokens as HTML
 */
function _j_htmlentities( $s ){
	if( $s === '' ){
		return '&nbsp;';
	}
	$s = htmlentities( $s, ENT_COMPAT, 'utf-8');
	$s = str_replace( array(' ',"\t"), array('&nbsp;','&nbsp;&nbsp;&nbsp;'), $s );
	return $s;
}


/**
 * We're going to need one extra utility to see if a token is a word.
 * This function accesses the JLex helper class directly.
 */
function _j_token_is_word( array $token ){
	$Lex = JLex::singleton();
	return is_array($token) && $Lex->is_word($token[1]);
}

/**
 * Build lines of highlighted source code
 */
$lines = array('');
$line =& $lines[0];
while( list(,$token) = each($tokens) ){
	list( $t, $s, $l, $c ) = $token;
	// generate a CSS class name for this token
	if( $s === 'true' || $s === 'false' || $s === 'null' ){
		$class = 'J_LITERAL';
	}
	else if( _j_token_is_word($token) ){
		$class = 'J_KEYWORD';
	}
	else if( ! is_int($t) && $s === $t ){
		$class = 'J_PUNCTUATOR';
	}
	else {
		$class = j_token_name($t);
	}
	// style and push onto source code lines array
	while( isset($s{0}) ){
		if( ! preg_match('/^(.*)(\n|\r\n|\r)/', $s, $r ) ){
			$lines[0] .= '<span class="'.$class.'">'._j_htmlentities($s).'</span>';
			break;
		}
		$lines[0] .= '<span class="'.$class.'">'._j_htmlentities($r[1]).'</span>';
		array_unshift( $lines, '' );
		$s = substr( $s, strlen($r[0]) );
	}
}

// We have lines of highlighted source code - display in HTML document

echo '<?xml version="1.0" encoding="UTF-8" ?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>jTokenizer example :: j_token_highlight</title>
<link rel="stylesheet" type="text/css" href="j_token_highlight.css" />
</head>
<body>

<ol class="javascript">
<?php
// print out lines as ordered list
$i = 0;
while( $line = array_pop($lines) ){
	$class = ++$i & 1 ? 'odd' : 'even';
	echo '<li class="',$class,'">',$line,"</li>\n";
}
?>
</ol>

</body>
</html>