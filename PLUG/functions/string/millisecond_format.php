<?php

namespace PLUG\functions\string;

/**
 * @category PLUG
 * @package functions
 * @subpackage string
 * @version $Id: millisecond_format.php,v 1.1 2009/03/22 12:44:44 twhitlock Exp $
 */
 
 

/**
 * Utility function formats a number of milliseconds into larger denominations
 * @param int milliseconds
 * @return string
 */	 
function millisecond_format( $ms ){
	// format as milliseconds if less than a second
	if( $ms <= 1000 ){
		return sprintf( '%d milliseconds', $ms );
	}
	// format as decimal seconds if less than a minute
	if( $ms < 60000 ){
		return sprintf( '%f seconds', $ms / 1000 );
	}
	// else format as hh:mm:ss
	$h = floor( $ms / 3600000 );
	$ms -= $h * 3600000;
	$m = floor( $ms / 60000 );
	$ms -= $m * 60000;
	$s = round( $ms / 1000 );
	return sprintf('%02d:%02d:%02d', $h, $m, $s );
}
