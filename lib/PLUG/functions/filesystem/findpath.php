<?php
/**
 * File containing function findpath
 * @author Tim Whitlock
 * @category PLUG
 * @package functions
 * @subpackage filesystem
 * @version $Id: findpath.php,v 1.1 2009/03/22 12:44:44 twhitlock Exp $
 */

/** require path cleaning function */
import('PLUG.functions.filesystem.cleanpath');


/**
 * Resolve a given path against currently defined include paths
 * @param string unresolved relative path
 * @param string optional current working directory
 * @param array optional additional paths to search
 * @param bool optionally avoid using include_path ini setting
 * @return string full path to resolved location
 */
function findpath( $f, $cwd = '.', array $extra = null, $noini = false ){
	if( $f == '' ){
		return $cwd;
	}
	if( $f{0} === '/' ){
		return cleanpath( $f );
	}
	$incs = $noini ? array() : explode( ':', ini_get('include_path') );
	if( ! is_null($extra) ){
		$incs = array_merge( $incs, $extra );
	}
	foreach( $incs as $inc ){
		if( $inc === '.' ){
			// current dir
			$inc = $cwd;
		}
		else if( $inc{0} !== '/' ){
			// relative path as include path, bad idea
			$inc = $cwd . $inc;
		}
		if( $f{0} !== '/' && substr($inc,-1,1) !== '/' ){
			// glue required
			$path = $inc . '/' . $f;
		}
		else {
			// already has glue
			$path = $inc . $f;
		}
		if( file_exists( $path ) ){
			return cleanpath( $path );
		}
	}
	// no path matched
	return null;
}
 