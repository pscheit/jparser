<?php

namespace PLUG\functions\filesystem;

/**
 * File containing function cleanpath
 * @author Tim Whitlock
 * @category PLUG
 * @package functions
 * @subpackage filesystem
 * @version $Id: cleanpath.php,v 1.1 2009/03/22 12:44:44 twhitlock Exp $
 */

 
 
/**
 * Neaten path with dots and extra slashes etc.
 * Use instead of {@link realpath} to avoid resolution of symlinks
 * @example functions/filesystem/cleanpath.php
 * @param string original path
 * @return string clean path
 */	
function cleanpath( $path ){
	if( ! $path ){
		return '';
	}
	$source = explode( '/', $path );
	$target = array_splice( $source, 0, 1 );
	foreach( $source as $i => $f ){
		if( $f === '.' || $f === '' ){
			// redundant reference to self
		}
		else if( $f === '..' ){
			// up a level if possible
			$f = end( $target );
			if( ! $f || $f === '..' ){
				// check it hasn't gone above root
				if( $path{0} === '/' ){
					trigger_error('Path goes above root', E_USER_NOTICE );
					return '/';
				}
				// else we have to keep the ..
				$target[] = '..';
			}
			// else ok to drill up.
			else {
				array_pop( $target );
			}
		}
		else {
			$target[] = $f;
		}
	}
	$cpath = implode('/', $target );
	if( $cpath === '' && $path{0} === '/' ){
		return '/';
	}
	return $cpath;
}