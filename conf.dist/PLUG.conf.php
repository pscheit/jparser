<?php
/**
 * PLUG Configuration.
 * Standard site/server configuration for a PLUG integration
 * @package conf
 */

/** Global error reporting level */
define('PLUG_ERROR_REPORTING', E_ALL | E_STRICT );  

/** Level of errors to be logged to standard error log */
define('PLUG_ERROR_LOGGING', E_ALL | E_STRICT ); 

/** Virtual host directory */
define('PLUG_HOST_DIR', realpath(__DIR__.DIRECTORY_SEPARATOR.'..'));

/** Virtual document root directory */
define('PLUG_VIRTUAL_DIR', PLUG_HOST_DIR.DIRECTORY_SEPARATOR.'httpdocs' );

/** temporary directory */
define('PLUG_TMP_DIR', '/tmp' );

/** Standard base URL for main site */
define('PLUG_HTTP_ADDR', 'http://my-jparser-host.local');

/** Secure base URL if available */
// define('PLUG_HTTPS_ADDR', NULL);



# Session configuration
#

/** Name for standard Session Cookie instead of `PHPSESSID' */
define('PLUG_SESSION_NAME', 'PHPSESSID');

/** Length [in minutes] of php session timeout */
define( 'PLUG_SESSION_TIMEOUT', 10 );
