<?php

namespace PLUG\parsing\utils;

/**
 * File containing function collect_encapsed_string
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage utils
 * @version $Id: collect_encapsed_string.php,v 1.1 2009/03/22 12:55:31 twhitlock Exp $
 */

 
 
/**
 * Collect an encapsed string
 * @param string chunk starting with a quote character
 * @param bool optionally allow line breaks
 * @return string substring containing delimited string
 */
function collect_encapsed_string( $src, $canbreak = false ){
	if( $canbreak ){
		$pattern = sprintf( '/^%1$s(?:\\\.|[^%1$s\\\])*%1$s/s', $src{0} );
	}
	else {
		$pattern = sprintf( '/^%1$s(?:\\\.|[^\r\n%1$s\\\])*%1$s/', $src{0} );
	}
	if( ! preg_match($pattern, $src, $r ) ){
		return '';
	}
	else {
		return $r[0];
	}
}
 
