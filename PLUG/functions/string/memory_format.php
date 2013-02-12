<?php
/**
 * @category PLUG
 * @package functions
 * @subpackage string
 */
 
 

/**
 * Utility function formats a number of bytes in larger denominations
 * @param int
 * @return string
 */	 
function memory_format( $n ){
	$units = array( 'bytes', 'Kb', 'Mb', 'Gb', 'Terabytes', 'Petabytes' );
	$i = 0;
	$dp = 0;
	while( $n >= 1024 ){
		$i++;
		$dp++;
		$n /= 1024;
	}
	$s = number_format( $n, $dp, '.', ',' );
	return sprintf("%s %s", $s, $units[$i] );
}
