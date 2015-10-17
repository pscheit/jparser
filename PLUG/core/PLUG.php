<?php

namespace PLUG\core;

/**
 * File containing top-level PLUG class
 * @category PLUG
 * @package core
 * @version $Id: PLUG.php,v 1.2 2009/08/09 23:39:42 twhitlock Exp $
 */

use Exception;

/**
 * Import PLUGException class
 */
use PLUG\core\PLUGException;

/**
 * Import PLUGError class
 */
use PLUG\core\PLUGError; 


/**
 * Top-level PLUG class
 * @category PLUG
 * @package core
 */
final class PLUG {


	/**
	 * Registered function for getting current system user.
	 * @static
	 * @var string | array
	 */
	private static $current_user_func;


	/**
	 * Initialize PLUG
	 * @return Void
	 */
	static function init(){
		
		// Start error handling asap
		self::set_error_handlers();
		// PLUG_ERROR_REPORTING should be set in conf
		if( defined('PLUG_ERROR_REPORTING') ){
			error_reporting( PLUG_ERROR_REPORTING );
		}
		else {
			define('PLUG_ERROR_REPORTING', error_reporting() );
		}
		
		// Select execution mode ( Server | Command line interface )
		if( defined('PLUG_CLI') ){
			// PLUGCli must have been included to define this constant
			PLUGCli::init();
		}
		else {
			// Default to server module mode
			// already checked PHP_SAPI
			define( 'PLUG_CLI', false );
		}
	}
	
	
	
	
	/**
	 * Test whether current script is compiled (optimized)
	 * @return bool
	 */
	static function is_compiled() {
		return PLUG_COMPILED;
	}
	
	
	
	/**
	 * Execute a script in the PLUG/bin directory.
	 * This is only a simple wrapper to PHP's built-in exec function.
	 * @param string path to script only, relative paths are assumed relative to PLUG/bin directory
	 * @param array script arguments
	 * @param resource|string reference for output
	 * @return int script exit value
	 */	
	static function exec_bin( $path, array $args = array(), &$stdout ){
		if( $path{0} !== '/' && $path{0} !== '.' ){
			$path = PLUG_HOST_DIR.'/PLUG/bin/'.$path;
		}
		$command = escapeshellcmd( $path );
		foreach( $args as $arg ){
			$command .= ' '.escapeshellarg( $arg );
		}
		exec( $command, $r, $e );
		$output = implode( "\n", $r );
		if( $e !== 0 ){
			trigger_error("command ``$command'' exited $e, $output", E_USER_WARNING );
		}
		
		if( is_resource($stdout) ){
			fwrite( $stdout, $output );
			fclose( $stdout );
		}
		else {
			$stdout = $output;
		}
		return $e;
	}	
	
	
	

	

	// --- ERROR HANDLING FUNCTIONS ---------------------
	// --------------------------------------------------
	
	
	
	/**
	 * Initialize error handling
	 * @return Void
	 */
	static function set_error_handlers() {
		set_error_handler( array(self::class, 'on_trigger_error' ) );
		set_exception_handler( array(self::class, 'on_uncaught_exception' ) );
	}
	
	
	
	
	/**
	 * restore original error handlers
	 * @return Void
	 */
	static function clear_error_handlers() {
		// restore global handlers
		restore_error_handler();
		restore_exception_handler();
	}
	
	
	
	
	/**
	 * @internal
	 * @param int php error level constant
	 * @param string error message
	 * @param string file path
	 * @param string line number
	 * @param string array arguments from calling scope
	 * @return void
	 */
	static function on_trigger_error( $type, $message, $file, $line, array $args ){
		if( error_reporting() === 0 ){
			// suppressed with `@' operator
			return;
		}
		self::clear_error_handlers();
		$trace = debug_backtrace();
		$callee = array_shift( $trace );
		$Err = new PLUGError( -1, $type, $message, $file, $line, $trace );
		$Err->raise();
		
		// Tell originating object about this error.
		// WARNING: "this" will only be in args list if it was referenced explicitly in the triggering scope !
		if( isset($args['this']) && method_exists($args['this'], 'on_trigger_error' ) ){
			$args['this']->on_trigger_error( $Err );
		}
		
		self::set_error_handlers();
	}	
	
	
	
	
	/**
	 * @internal
	 * @param Exception
	 * @return Void
	 */
	static function on_uncaught_exception( Exception $Ex ){
		self::raise_exception( $Ex, E_USER_ERROR );
	}	
	
	
	
	
	/**
	 * Force the raising of an error
	 * @internal
	 * @param string error message
	 * @param int error code
	 * @param int php error type constant
	 * @param array backtrace
	 * @return PLUGError reference to raised error
	 */
	static function raise_error( $code, $message, $type, array $trace = null ){
		if( error_reporting() === 0 ){
			// suppressed with `@' operator
			return;
		}
		self::clear_error_handlers();
		if( is_null($trace) ){
			$trace = debug_backtrace();
			array_shift( $trace );
		}
		$callee = current( $trace ); 
		$Err = new PLUGError( $code, $type, $message, $callee['file'], $callee['line'], $trace );
		$Err->raise();
		self::set_error_handlers();
		return $Err;
	}
	
	
	

	/**
	 * Raise an error from an Exception
	 * @param Exception
	 * @param int php error type constant
	 * @return PLUGError
	 */	
	static function raise_exception( Exception $Ex, $type ){
		if( error_reporting() === 0 ){
			// suppressed with `@' operator
			return;
		}
		PLUG::clear_error_handlers();
		$code = $Ex->getCode() or $code = PLUG_EXCEPTION_STD;
		$Err = new PLUGError( $code, $type, $Ex->getMessage(), $Ex->getFile(), $Ex->getLine(), $Ex->getTrace() );
		$Err->raise();
		PLUG::set_error_handlers();
		return $Err;
	}
	
	
	
	
	/**
	 * Dump all raised errors to output
	 * @param int php error level constant
	 * @return Void
	 */	
	static function dump_errors( $emask = PLUG_ERROR_REPORTING ){
		$errs = PLUGError::get_errors( $emask );
		foreach( $errs as $Err ){
			$s = $Err->__toString(). "\n";
			if( PLUG_CLI ){
				PLUGCli::stderr( $s );
			}
			else {
				echo $s;
			}
		}
	}
	
	
	
	
	/**
	 * @param int php error level constant
	 * @return bool
	 */	
	static function is_error( $emask = PLUG_ERROR_REPORTING ){
		return PLUGError::is_error( $emask );
	}	
	
	
	
	/**
	 * @param int php error level constant
	 * @return array
	 */	
	static function get_errors( $emask = PLUG_ERROR_REPORTING ){
		return PLUGError::get_errors( $emask );
	}	
	
	
	
	
	/**
	 * @param int php error level constant
	 * @return array
	 */	
	static function clear_errors( $emask = null ){
		$errs = PLUGError::get_errors( $emask );
		foreach( $errs as $Err ){
			$Err->clear();
		}
	}
	
	
	
	/**
	 * Register error display function.
	 * @param string | array handler callable with call_user_func
	 * @return bool
	 */	
	static function set_error_display( $handler ){
		if( ! is_callable($handler) ){
			trigger_error( 'Error display handler is not callable ('.var_export($handler,1).')', E_USER_WARNING );
			return false;
		}
		PLUGError::$displayfunc = $handler;
		return true;
	}		
	
	
	/**
	 * Register error logging function.
	 * @param string | array handler callable with call_user_func
	 * @return bool
	 */	
	static function set_error_logger( $handler ){
		if( ! is_callable($handler) ){
			trigger_error( 'Error logging handler is not callable ('.var_export($handler,1).')', E_USER_WARNING );
			return false;
		}
		PLUGError::$logfunc = $handler;
		return true;
	}
	
	
	/**
	 * Register script termination function
	 * @param string | array handler callable with call_user_func
	 * @return bool
	 */	
	static function set_error_terminator( $handler ){
		if( ! is_callable($handler) ){
			trigger_error( 'Fatal error handler is not callable ('.var_export($handler,1).')', E_USER_WARNING );
			return false;
		}
		PLUGError::$deathfunc = $handler;
		return true;
	}
	
	
	
	/**
	 * Register a callback for getting the current system user's id, or name.
	 * - This is useful for logging errors etc..
	 * - self::current_user() will return 0, unless you define a handler.
	 * @param string | array handler callable with call_user_func
	 * @return bool
	 */	
	static function set_current_user_getter( $handler ){
		if( ! is_callable($handler) ){
			trigger_error( 'current user getter is not callable ('.var_export($handler,1).')', E_USER_WARNING );
			return false;
		}
		self::$current_user_func = $handler;
		return true;	
	}		
	
	
	
	
	/**
	 * Get id, or name of current system user.
	 * @return int | string
	 */	
	static function current_user(){
		if( is_null(self::$current_user_func) ){
			return 0;
		}
		return call_user_func( self::$current_user_func );
	}


	

}
