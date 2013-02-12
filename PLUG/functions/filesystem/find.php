<?php
/**
 * File containing function PLUG.functions.find
 * @author Tim Whitlock
 * @category PLUG
 * @package functions
 * @subpackage filesystem
 */






/**
 * Simple interface to shell find program.
 * - Useful for listing directoried recursively with a simple search pattern.
 * - This is much faster than {@see preg_find} use it if you dont't require regexp matching.
 * @todo support other flags other than -name
 * @param string path to search under
 * @param string simple search pattern, e.g. *.php
 */
function find( $path, $name ){
	$command = 'find '.escapeshellarg($path).' -name '.escapeshellarg($name);
	exec( $command, $lines, $e );
	if( $e ){
		trigger_error("find exited $e, ".implode("\n",$lines), E_USER_WARNING );
		return array();
	}
	return $lines;
}
