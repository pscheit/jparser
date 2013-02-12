<?php
/** 
 * Compile-time include for running from command line.
 * @version $Id: plugcli_c.php,v 1.1 2009/03/22 13:02:11 twhitlock Exp $
 * @category PLUG
 * @package core
 */

/** 
 * Flag environment as being deployed
 */
define( 'PLUG_COMPILED', true );

/**
 * Change working directory to script file
 */
chdir( dirname($argv[0]) );

/**
 * Include target configuration in `conf' directory outside document root
 */
import( 'conf.PLUG' ); 

/**
 * Import top-level PLUG class
 */ 
import('PLUG.core.PLUG'); 

/**
 * Import Command line interface before intializing
 */ 
import('PLUG.core.PLUGCli'); 

/**
 * Initialize PLUG environment
 */
PLUG::init();
