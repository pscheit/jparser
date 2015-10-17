<?php

namespace PLUG\functions\filesystem;

/**
 * File containing function tempfile
 * @author Tim Whitlock
 * @category PLUG
 * @package functions
 * @subpackage filesystem
 * @version $Id: tempfile.php,v 1.1 2009/03/22 12:44:44 twhitlock Exp $
 */

use Exception;

/**
 * Improvement to php tmpfile function because it provides us with file path via tempnam
 * @example functions/filesystem/tempfile.php
 * @param resource reference to file pointer variable
 * @param string optional fopen mode, defaults to "w", pass empty to avoid opening file
 * @param string optional prefix for tempnam, defaults to "plug"
 * @param bool optionally unlink file on shutdown, defaults to true 
 * @return string path to tmp file
 */	
function tempfile( &$rsc, $mode = 'w', $prefix = 'plug', $unlink = true ){
	if( is_resource($rsc) ){
		trigger_error( "You should pass an empty reference to tempfile ($rsc)", E_USER_WARNING );
		fclose( $rsc );
	}
	$path = tempnam( PLUG_TMP_DIR, $prefix );
	if( !$path ){
		throw new Exception("tempnam failed in tempfile");
	}
	if( $unlink ){
		register_shutdown_function( 'unlink', $path );
	}
	if( $mode ){
		$rsc = fopen( $path, $mode );
		if( ! is_resource($rsc) ){
			throw new Exception("fopen failed in tempfile with mode $mode");
		}
	}
	return $path;
}
