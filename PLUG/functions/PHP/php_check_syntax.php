<?php

namespace PLUG\functions\PHP;

/**
 * File containing function php_check_syntax
 * @author Tim Whitlock
 * @category PLUG
 * @package functions
 * @subpackage PHP
 * @version $Id: php_check_syntax.php,v 1.1 2009/03/22 12:44:44 twhitlock Exp $
 */

 
 
/**
 * Replacement as this function was removed from PHP as from 5.0.5
 * @example functions/PHP/php_check_syntax.php
 * @param string file path
 * @param string reference for error description
 */
function php_check_syntax( $filepath, &$err ){
	$command = 'php -l '.escapeshellarg($filepath). ' 2>&1';
	exec( $command, $r, $e );
	if( $e !== 0 ){
		$err = implode( "\n", $r );
		return false;
	}
	return true;
}	
