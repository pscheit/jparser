<?php

namespace PLUG\core;

/**
 * File containing PLUGCli Class
 * @version $Id: PLUGCli.php,v 1.1 2009/03/22 12:42:21 twhitlock Exp $
 * @author Tim Whitlock
 * @category PLUG
 * @package core
 * @subpackage 
 */


/**
 * Sanity check in case this was imported after PLUG initialization
 */ 
if( defined('PLUG_CLI') ){
	trigger_error( "PLUG.core.PLUGCli imported in bad context; PLUG_CLI=".var_export(PLUG_CLI,1)."; see plugcli.php", E_USER_ERROR );
}
  
 
/**
 * Constant defines that script is intended to run from command line 
 */ 
define( 'PLUG_CLI', true );
 
 
/**
 * PLUG Command Line Interface
 * @category PLUG
 * @package core
 */
final class PLUGCli {

	/**
	 * @var array
	 */	
	private static $args = array();


	/**
	 * Initialize PLUG CLI environment
	 * @todo research CGI environment, differences to cli
	 * @return Void
	 */
	static function init() {

		switch( PHP_SAPI ) {
		// Ideally we want to be runnning as CLI
		case 'cli':
			break;
		// Special conditions to ensure CGI runs as CLI
		case 'cgi':
			// Ensure resource constants are defined
			if( ! defined('STDERR') ){
				define( 'STDERR', fopen('php://stderr', 'w') );
			}
			if( ! defined('STDOUT') ){
				define( 'STDOUT', fopen('php://stdout', 'w') );
			}
			break;
		default:
			echo "Command line only\n";
			exit(1);
		}
		
		// Default error logger function
		PLUG::set_error_logger( array(__CLASS__,'format_logline') );
		
		// parse command line arguments from argv global
		global $argv, $argc;
		// first cli arg is always current script. second arg will be script arg passed to shell wrapper
		for( $i = 1; $i < $argc; $i++ ){
			$arg = $argv[ $i ];
			
			// Each command line argument may take following forms:
			//  1. "Any single argument", no point parsing this unless it follows #2 below
			//  2. "-aBCD", one or more switches, parse into 'a'=>true, 'B'=>true, and so on
			//  3. "-a value", flag used with following value, parsed to 'a'=>'value'
			//  4. "--longoption", GNU style long option, parse into 'longoption'=>true
			//  5. "--longoption=value", as above, but parse into 'longoption'=>'value'
			//  6."any variable name = any value" 
			
			$pair = explode( '=', $arg, 2 );
			if( isset($pair[1]) ){
				$name = trim( $pair[0] );
				if( strpos($name,'--') === 0 ){
					// #5. trimming "--" from option, tough luck if you only used one "-"
					$name = trim( $name, '-' );
				}
				// else is #6, any pair
				$name and self::$args[$name] = trim( $pair[1] );
			}
			
			else if( strpos($arg,'--') === 0 ){
				// #4. long option, no value
				$name = trim( $arg, "-\n\r\t " );
				$name and self::$args[ $name ] = true;
			}
			
			else if( $arg && $arg{0} === '-' ){
				$flags = preg_split('//', trim($arg,"-\n\r\t "), -1, PREG_SPLIT_NO_EMPTY );
				foreach( $flags as $flag ){
					self::$args[ $flag ] = true;
				}
				// leave $flag set incase a value follows.
				continue;
			}

			// else is a standard argument. use as value only if it follows a flag, e.g "-a apple"
			else if( isset($flag) ){
				self::$args[ $flag ] = trim( $arg );
			}

			// dispose of last flag
			unset( $flag );
			// next arg 
		}
	}	
	
	
	
	
	/**
	 * Get command line argument
	 * @param int|string argument name or index
	 * @param string optional default argument to return if not present
	 * @return string
	 */	
	final static function arg( $a, $default = null ){
		if( is_int($a) ){
			global $argv;
			// note: arg(0) will always be the script path
			return isset($argv[$a]) ? $argv[$a] : $default;
		}
		else {
			return isset(self::$args[$a]) ? self::$args[$a] : $default;
		}
	}
	
	
	
	/**
	 * Print to stderr
	 * @param string printf style formatter
	 * @param ... arguments to printf
	 * @return void
	 */
	final static function stderr( $s ){
		if( func_num_args() > 1 ){
			$args = func_get_args();
			$s = call_user_func_array( 'sprintf', $args );
		}
		fwrite( STDERR, $s );
	}
	
	
	
	/**
	 * Standard error logger printing to stderr with program name
	 */
	static function format_logline( PLUGError $Err ){
		if( PLUG::is_compiled() ){
			return sprintf(
				'%s: %s: %s', 
				basename(self::arg(0)), 
				$Err->getTypeString(), 
				$Err->getMessage()
			);
		}
		else {
			return sprintf(
				'%s: %s: %s in %s#%u', 
				basename(self::arg(0)), 
				$Err->getTypeString(), 
				$Err->getMessage(), 
				basename($Err->getFile()), 
				$Err->getLine()
			);
		}
	}

	
	
	/**
	 * Print to stdout
	 * @param string printf style formatter
	 * @param ... arguments to printf
	 * @return void
	 */
	final static function stdout( $s ){
		if( func_num_args() > 1 ){
			$args = func_get_args();
			$s = call_user_func_array( 'sprintf', $args );
		}
		echo $s;
	}
	
	
} 



/**
 * cli replacement for php {@link virtual} function
 * @param string virtual path
 * @return void
 */
function virtual( $vpath ){
	if( $vpath{0} === '/' ){
		$vpath = PLUG_VIRTUAL_DIR . $vpath;
	}
	readfile( $vpath );
}

 
 
