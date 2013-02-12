<?php
/**
 * File containing function bnf_token_dump
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage bnf
 * @version $Id: bnf_token_dump.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */


/**
 * Require BNF Lexicon
 */
import('PLUG.parsing.bnf.BNFLex');
 


/**
 * Debugging function to print javascript token stream
 * @param array
 * @return void
 */	
function bnf_token_dump( array $tokens ){
	$Lex = new BNFLex;
	$line = 0;
	foreach( $tokens as $token ){
		
		list( $key, $value, $l ) = $token;

		//if( $key === J_WHITESPACE ){
		//		continue;
		//}

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

?>