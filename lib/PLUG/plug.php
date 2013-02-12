<?php
/**
 * Development runtime include.
 * Includes essential PLUG components
 * @version plug.php,v 1.2 2007/11/27 21:10:48 animal Exp
 * @category PLUG
 * @package core
 */


/** 
 * Flag environment as being source
 */
define( 'PLUG_COMPILED', false );


// Die if not running in server mode
switch( PHP_SAPI ) {
case 'cgi': 
case 'cli': 
	echo "ERROR: This script is not intended to run from the command line, see plugcli.php\n";
	exit( -1 );
}

 
/**
 * Include development configuration in `conf' directory outside document root
 */
require $_SERVER['DOCUMENT_ROOT'].'/../conf/PLUG.conf.php'; 


/**
 * Include import tool for development mode
 */
require PLUG_HOST_DIR.'/PLUG/core/PLUGTool.php'; 


/**
 * Import top-level PLUG class
 */ 
import('PLUG.core.PLUG'); 


/**
 * Initialize PLUG environment
 */
PLUG::init();
