<?php

namespace PLUG\functions\filesystem;

/**
 * File containing function PLUG.functions.filesystem.preg_find
 * @author Tim Whitlock
 * @category PLUG
 * @package functions
 * @subpackage filesystem
 */






/**
 * Collects files according to a regular expression.
 * - Useful for listing directories recursively.
 * - This is much slower than {@see find}, because it uses {@see readdir} and {@see opendir}
 * - Note that expression only acts on file names, and not directories
 * @param string path to search under
 * @param string preg_match compatible regular expression, e.g. /\.php$/
 * @param bool whether to descend into sub directories, defaults to false
 * @return array
 */
function preg_find( $path, $pattern, $recursive = false ){
	$found = array();
	_preg_find_recursive( $path, $pattern, $recursive, $found );
	return $found;
}



/**
 * @ignore
 */
function _preg_find_recursive( $path, $pattern, $recursive, array &$found ){
	$dhandle = opendir( $path );
	if( ! is_resource($dhandle) ){
		return;
	}
	while( false !== $f = readdir($dhandle) ){
		if( $f === '.' || $f === '..' ){
			continue;
		}
		$nextpath = $path.'/'.$f;
		// recurse depth first into directories
		if( is_dir($nextpath) ){
			if( $recursive ){
				_preg_find_recursive( $nextpath, $pattern, $recursive, $found );
			}
		}
		// collect if no pattern passed
		if( ! $pattern ){
			$found[] = $nextpath;
		}
		// else collect file if pattern matches
		else if( preg_match( $pattern, $f ) ){
			$found[] = $nextpath;
		}
	}
	closedir($dhandle);
}












