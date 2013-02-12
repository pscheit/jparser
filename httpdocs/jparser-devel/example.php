<?php
/**
 * Example shows how to import the jParser classes for library development
 * Be sure to set up your system config in conf/PLUG.conf.php
 */

// PLUG framework is required to import classes
require '../../PLUG/plug.php';

// My aswell import everything from the JavaScript package
import('PLUG.JavaScript.*');


// Using the JTokenizer class directly
$src = 'alert("Hello World")';
$Tokenizer = new JTokenizer( true, true );
$tokens = $Tokenizer->get_all_tokens( $src );
//var_dump( $tokens );


// Using the JParser class directly
try {
	$Parser = new JParser;
	$Tree = $Parser->parse( $tokens );
}
catch( ParseError $Ex ){
	$error = $Ex->getMessage()."\n----\n".$Ex->snip($src);
	die('<pre>'.htmlentities($error,ENT_COMPAT,'UTF-8').'</pre>');
}


// PLUG buffers errors, so we need to see if anything went wrong.
if( PLUG::is_error() ){
	PLUG::dump_errors();
}
// else no errors occured
else {
	echo 'Done without error.';
}


