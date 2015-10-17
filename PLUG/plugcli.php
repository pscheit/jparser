<?php

namespace PLUG;

/**
 * Development runtime include for running from command line.
 * Include this instead of plug.php when writing a command line script.
 * It includes essential PLUG components and starts up PLUGCli
 * @version plugcli.php,v 1.8 2008/06/17 22:11:34 animal Exp
 * @category PLUG
 * @package core
 */

/** 
 * Flag environment as being source
 */
define( 'PLUG_COMPILED', false );


// Die if running in server mode
switch( PHP_SAPI ) {
case 'cgi': 
case 'cli': 
	break;
default:
	// show correct terminal command to execute this script
	$h = realpath( "{$_SERVER['DOCUMENT_ROOT']}/.." );
	$f = str_replace( "$h/", '', $_SERVER['SCRIPT_FILENAME'] );
	echo "<pre>",
		"This script is intended to run from the command line only.\n",
		"Issue the following shell command to execute this script:\n\n",
		" php -f ",escapeshellarg($f),"\n\n",
		"</pre>";
	exit( -1 );
}


/**
 * Calculate directory script that included this file without resolving symlinks.
 * This is important because if the library source is symlilnked the confs will be under a different tree
 * note: __FILE__ points to the realpath
 */
if( isset($__cwd) ){
	trigger_error('Do not define $cwd before including this file', E_USER_ERROR );
}
$__cwd = dirname( $argv[0] );
if( $__cwd{0} !== '/' ){
	// relative cwd, must prepend current working directory
	// getcwd() resolves symlinks, must use shell prog
	$__cwd = getcwd() .'/'. $__cwd;
}
chdir( $__cwd ) or trigger_error( "Failed to cd to $__cwd", E_USER_ERROR );

/**
 * Include development configuration in `conf' directory outside document root
 * We have to crawl up the current path until we find the conf file.
 */
$apath = explode( '/', $__cwd );
foreach( $apath as $f ){
	$confpath = implode('/', $apath ).'/conf/PLUG.conf.php';
	if( file_exists($confpath) ){
		require $confpath;
		unset( $apath );
		unset( $confpath );
		break;
	}
	// else drill up
	array_pop( $apath );
}


/**
 * conf file must have defined PLUG_HOST_DIR
 */
if( isset($confpath) ){
	trigger_error( "Failed to find `conf/PLUG.conf.php' above `$__cwd'", E_USER_ERROR );
	exit( -1 );
}
else if( ! defined('PLUG_HOST_DIR') ){
	trigger_error( "PLUG.conf.php must define PLUG_HOST_DIR", E_USER_ERROR );
	exit( -1 );
}

unset( $__cwd );

/**
 * Include import tool for development mode
 */
require PLUG_HOST_DIR.'/PLUG/core/PLUGTool.php'; 


/**
 * Import top-level PLUG class
 */ 
use PLUG\core\PLUG; 


/**
 * Import Command line interface before intializing
 */ 
use PLUG\core\PLUGCli; 


/**
 * Initialize PLUG environment
 */
PLUG::init();


