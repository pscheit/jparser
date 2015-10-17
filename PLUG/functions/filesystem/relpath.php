<?php

namespace PLUG\functions\filesystem;

/**
 * File containing function relpath
 * @author Tim Whitlock
 * @category PLUG
 * @package functions
 * @subpackage filesystem
 * @version $Id: relpath.php,v 1.1 2009/03/22 12:44:44 twhitlock Exp $
 */


/** require path cleaning function */
use function PLUG\functions\filesystem\cleanpath;
 
 
/**
 * Map a relative path from a working directory to a target file
 * - this will always return a relative path, even if target is in the root
 * @example functions/filesystem/relpath.php
 * @param string absoulte path to current working directory
 * @param string absolute path to target 
 * @param array optional include paths, this setting overrides the following.
 * @param bool optionally ommit leading single dot, e.g. './here' becomes 'here'
 * @return string relative path to target from cwd
 */	
function relpath( $thisdir, $target, array $incs = null, $nodot = false ){

	if( $target{0} !== '/' ){
		// target already relative
		return $target;
	}
	else if( $thisdir{0} !== '/' ){
		trigger_error( "first argument must be an absolute path", E_USER_NOTICE );
		return $target;
	}
	
	// important: this method will fail if paths have redundant references
	$thisdir = cleanpath( $thisdir );
	$target = cleanpath( $target );
	
	// support include paths, as base directory
	if( ! empty($incs) ){
		$paths = array();
		foreach( $incs as $inc ){
			if( $inc{0} !== '/' ){
				$inc = $thisdir.'/'.$inc;
			}
			$inc = cleanpath( $inc );
			$nodot = $inc !== $thisdir;
			$relpath = relpath( $inc, $target, null, $nodot );
			// log path by length
			$paths[ strlen($relpath) ] = $relpath;
		}
		// return shortest path calculated
		ksort( $paths );
		return current( $paths );
	}
	
	$filename = basename( $target );
	$athis = explode('/', $thisdir );
	$atarg = explode('/', dirname($target) );

	// at some point paths will branch, that's our common point.
	while( ! empty($athis) && ! empty($atarg) ){
		$fthis = $athis[0];
		$ftarg = $atarg[0];
		if( $ftarg !== $fthis ){
			// paths branch at this point
			break;
		}
		array_shift( $athis );
		array_shift( $atarg );
	}
	// target could be in the root
	$inroot = empty($atarg) || $atarg === array('');
	// target is below cwd if athis is empty
	if ( empty($athis) ){
		$apath = $nodot ? array() : array('.');
	}
	// else navigate up to common directory
	else {
		$apath = array_fill( 0, count($athis), '..' );
	}
	// drill down to target, unless relpath targets root
	if( ! $inroot ){
		$apath = array_merge( $apath, $atarg );
	}
	// append file name and we're there!
	$apath[] = $filename;
	return implode( '/', $apath );		
}


 
?>