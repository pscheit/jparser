<?php
/**
 * File containing function j_token_dump
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @version $Id: j_token_dump.php,v 1.1 2009/03/22 12:51:43 twhitlock Exp $
 */




/**
 * Debugging function to print javascript token stream
 * @param array
 * @return void
 */	
function j_token_dump( array $tokens, $LexClass = 'JLex' ){

	// instantiate Lex instance of appropriate type
	$Lex = Lex::get( $LexClass );

	$line = 0;
	foreach( $tokens as $token ){
		
		list( $key, $value, $l ) = $token;

		if( $key === J_WHITESPACE || $key === J_LINE_TERMINATOR ){
			continue;
		}

		// print line number
		if( $l !== $line ){
			$line = $l;
			echo "#$line.\n";
		}
		
		// show token
		if( $key === $value || is_null($key) ){
			echo " \"", $value, "\" \n";
		}
		else {
			echo " ", $Lex->name($key), " : \"", $value, "\" \n";
		}
	}


}

