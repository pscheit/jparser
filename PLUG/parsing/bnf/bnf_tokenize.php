<?php

namespace PLUG\parsing\bnf;

/**
 * File containing function bnf_tokenize
 * @category PLUG
 * @package parsing
 * @subpackage bnf
 */

 
use PLUG\parsing\bnf\BNFLex;
use function PLUG\parsing\utils\collect_encapsed_string;

/**
 * Tokenize a bnf string
 * @param string 
 * @return array
 */
function bnf_tokenize( $src ){
	if( $src == '' ){
		return array();
	}
	$line = 1;
	$col = 1;
	$tokens = array();
	$inrule = false;
	while( $src ){
	
		if( $src{0} === '"' || $src{0} === "'" ){
			$inrule = true;
			$t = BNF_LITERAL;
			$s = collect_encapsed_string( $src );
		}
		else if( preg_match('/^[\-\w]+/', $src, $r ) ){
			$inrule = true;
			$t = BNF_TEXT;
			$s = $r[0];
		}
		else if( preg_match('/^\s+/', $src, $r ) ){
			$s = $r[0];
			// easiest to catch rule termination here
			// double break (optionally with simple whitespace ) 
			if( $inrule && 1 < preg_match_all('/(?:\r\n|\n|\r)/', $s, $r ) ){
				$t = BNF_RULE_END;
				$inrule = false;
			}
			else {
				// discard insignificant whitespace now
				$t = false;
			}
		}
		else if( preg_match('/^#.*/', $src, $r ) ){
			// discard simple one line #comments
			$t = false;
			$s = $r[0];
		}
		//else if( preg_match('/^::=/', $src, $r ) ){
		//	// other simple multi-character tokens
		//	$inrule = true;
		//	$t = null;
		//	$s = $r[0];
		//}
		else {
			// else any unvalidated single character input
			$s = $src{0};
			switch( $s ){
			case ';':
				$t = BNF_RULE_END;
				$inrule = false;
				break;
			default:
				$inrule = true;
				$t = null;
			}
		}
		
		// append token
		if( $t === false ){
			// discard
		}
		else if( $t ){
			$tokens[] = array( $t, $s, $line, $col );
		}
		else {
			$tokens[] = array( $s, $s, $line, $col );
		}

		// truncate
		$len = strlen( $s );
		$src = substr( $src, $len );
		// calculate line number
		$nbreaks = preg_match_all( '/(\r\n|\n|\r)/', $s, $r );
		if( $nbreaks ){
			$line += $nbreaks;
			// calculate column number
			$cbreak = end( $r[0] );
			$npos = strrpos( $s, $cbreak );
			$col =  $len - $npos;
		}
		else {
			$col +=  $len;
		}
	}
	
	// ensure stream is terminated
	if( $t !== BNF_RULE_END ){
		$tokens[] = array( BNF_RULE_END, ';', $line, ++$col );
	}

	return $tokens;
}

?>