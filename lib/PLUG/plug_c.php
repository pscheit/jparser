<?php
/**
 * Compile-time include.
 * Includes essential PLUG components for compiled source.
 * @version $Id: plug_c.php,v 1.1 2009/03/22 13:02:11 twhitlock Exp $
 * @category PLUG
 * @package core
 */

/** 
 * Flag environment as being deployed
 */
define( 'PLUG_COMPILED', true );

/**
 * Include target configuration in `conf' directory outside document root
 */
import( 'conf.PLUG' ); 

/**
 * Import top-level PLUG class
 */ 
import('PLUG.core.PLUG'); 

/**
 * Initialize PLUG environment
 */
PLUG::init();