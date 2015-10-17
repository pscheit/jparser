<?php

namespace PLUG\core;

/**
 * File containing PLUGError class
 * @category PLUG
 * @package core
 * @version $Id: PLUGError.php,v 1.3 2009/07/28 23:39:59 twhitlock Exp $
 */

 
/**
 * Standard built-in PLUG error code
 */
define( 'PLUG_ERR_STD', -1 );


/**
 * Special error bit value - outside of PHP range.
 * This is used for internal errors that it makes no sense to suppress, example; failed login attempt.
 * Warning: This cannot be used with trigger_error(), only PLUG::raise_error
 */
define( 'E_USER_INTERNAL', 8192 ); // E_RECOVERABLE_ERROR << 1

/**
 * E_RECOVERABLE_ERROR was only introduced in PHP 5.2
 */
if( ! defined('E_RECOVERABLE_ERROR') ){
	define('E_RECOVERABLE_ERROR', 4096 );
}


/**
 * extra compound value to include all errors, except E_STRICT
 */
define( 'E_USER_ALL', E_ALL | E_USER_INTERNAL );


/**
 * PLUG Error object.
 * Note that this is not an Exception, it is a concrete error. <br />
 * Once an error is raised it is held in the stack and can be queried, displayed or cleared.
 * @example core/PLUGError.php
 * @category PLUG
 * @package core
 */
class PLUGError {
	
	/**
	 * descriptive names of error types
	 * @var array
	 */	
	private static $types = array (
		E_ERROR              => 'Fatal Error',
		E_WARNING            => 'Warning',
		E_PARSE              => 'Parsing Error',
		E_NOTICE             => 'Notice',
		E_CORE_ERROR         => 'Core Error',
		E_CORE_WARNING       => 'Core Warning',
		E_COMPILE_ERROR      => 'Compile Error',
		E_COMPILE_WARNING    => 'Compile Warning',
		E_USER_ERROR         => 'Error',
		E_USER_WARNING       => 'Warning',
		E_USER_NOTICE        => 'Notice',
		E_STRICT             => 'Runtime Notice',
		E_RECOVERABLE_ERROR  => 'Catchable Fatal Error',
		E_USER_INTERNAL      => 'Internal'
	);	

	/**
	 * Stack of all raised errors
	 * @var array
	 */
	private static $stack = array();

	/**
	 * Error stack identifier
	 * @var int
	 */
	private $id;
	
	/**
	 * Incremental stack identifier
	 * @var int
	 */
	private static $i = 0;
	
	/**
	 * Error code
	 * @var int
	 */
	private $code;

	/**
	 * Error type constant, e.g. E_USER_NOTICE
	 * @var int
	 */
	private $type;

	/**
	 * Error message text
	 * @var string
	 */
	private $message;
	
	/**
	 * File path where error was raised from
	 * @var string
	 */
	private $file;

	/**
	 * Line number where error was raised
	 * @var int
	 */
	private $line;

	/**
	 * backtrace stack from where error was raised
	 * @var array
	 */
	private $trace;
	
	/**
	 * Registered function for sending error to stdout
	 * @internal
	 * @var mixed
	 */	
	static $displayfunc = array( 'PLUGError', 'display' );
	
	/**
	 * Registered function for logging error or sending to stderr
	 * @internal
	 * @var mixed
	 */	
	static $logfunc = array( 'PLUGError', 'logdisplay' );
	
	/**
	 * Registered function for terminating script on fatal error
	 * @internal
	 * @var mixed
	 */	
	static $deathfunc = array( 'PLUGError', 'death' );
	
	
	/**
	 * Constructor
	 */	
	function __construct ( $code, $type, $message, $file, $line, array $trace ){
		$this->code = $code;
		$this->type = $type;
		$this->message = $message;
		$this->file = $file;
		$this->line = $line;
		$this->trace = $trace;
	}
	
	
	
	/**
	 * Get error code
	 * @return int
	 */
	public function getCode() {
		return $this->code;
	}
	
	
	
	/**
	 * Get error id
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}
	
	
	
	/**
	 * Get type of error, e.g. E_USER_NOTICE
	 * @return int
	 */
	public function getType() {
		return $this->type;
	}	
	
	
	/**
	 * Get string type of error, e.g. "Notice"
	 * @return int
	 */
	public function getTypeString() {
		return self::$types[ $this->type ]; 
	}	
	
	
	
	/**
	 * Get error message text
	 * @return string
	 */
	public function getMessage() {
		return $this->message;
	}	
	
	
	
	/**
	 * Get line number where error was raised
	 * @return string
	 */
	public function getLine() {
		return $this->line;
	}	
	
	
	
	/**
	 * Get file path from where error was raised
	 * @return string
	 */
	public function getFile() {
		return str_replace( realpath(PLUG_HOST_DIR), '', $this->file );
	}	
	
	
	
	/**
	 * Get backtrace from where error was raised
	 * @return string
	 */
	public function getTrace() {
		return $this->trace;
	}	
	
	
	
	/**
	 * Get backtrace from where error was raised as string as per built-in Exception class
	 * @return string
	 */
	public function getTraceAsString() {
		$lines = array ();
		for( $i = 0; $i < count($this->trace); $i++ ){
			$a = $this->trace[$i];
			$call = "{$a['function']}()";
			if( isset($a['class']) ){
				$call = "{$a['class']}{$a['type']}$call";
			}
			$lines[] = "#$i {$a['file']}({$a['line']}): $call";
		} 
		$lines[] = "#$i {main}";
		return implode( "\n", $lines );
	}	
	
	
	
	/**
	 * Test whether error is user-invoked as opposed to PHP-invoked
	 * @return bool
	 */	
	public function is_user_error() {
		switch( $this->type ){
		case E_USER_INTERNAL:
		case E_USER_WARNING:
		case E_USER_NOTICE:
		case E_USER_ERROR:
			return true;
		default:
			return false;
		}
	}
	
	
	
	
	/**
	 * Dispatch this error
	 * @todo callbacks to registered listeners
	 * @return Void
	 */
	public function raise() {
		// log error to file according to PLUG_ERROR_LOGGING
		if( PLUG_ERROR_LOGGING & $this->type ) {
			// send to standard, or configured log file 
			$logfile = defined('PLUG_ERROR_LOG') ? PLUG_ERROR_LOG : '';
			$logged = self::log( call_user_func(self::$logfunc,$this), $logfile );
		}
		// add to error stack if we are keeping this type if error
		// internal errors are always raised
		if( PLUG_ERROR_REPORTING & $this->type || $this->type === E_USER_INTERNAL ) {
			// register self as a raised error
			$this->id = self::$i++;
			self::$stack[ $this->type ][ $this->id ] = $this;
			// cli can pipe error to stderr, but not if the logging call already did.
			if( PLUG_CLI ){
				self::log( call_user_func(self::$logfunc,$this), STDERR );
			}
		}
		// call exit handler on fatal error
		// @todo - configurable fatal level
		$fatal = E_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;
		if( $this->type & $fatal ){
			call_user_func( self::$deathfunc, $this );
		}
	}
	

	
	
	/**
	 * Default script exit handler
	 * @param PLUGError
	 * @return void
	 */
	private static function death( PLUGError $Err ){
		if( PLUG_CLI ){
			// Print final death message to stderr if last error was logged
			$logfile = ini_get('error_log');
			if( $logfile ){
				PLUGCli::stdout("Error, %s exiting %s\n", $Err->getMessage(), $Err->code );
			}
		}
		else {
			// display all errors in browser
			PLUG::dump_errors();
		}
		exit( $Err->code );
	}
	
	
	
	
	
	/**
	 * Log an error.
	 * @param PLUGError
	 * @return string formatted error string
	 */	
	private static function logdisplay( PLUGError $Err ) {
		$logline = sprintf ( 
			'[uid:%s] %s: %s in %s on line %u',
			PLUG::current_user(),
			$Err->getTypeString(),
			$Err->getMessage(), 
			$Err->getFile(),
			$Err->getLine()
		);
		// Add data to log that some SAPIs miss out.
		switch( PHP_SAPI ){
		// Apache2 doesn't date stamp the error log.
		case 'apache2handler':
			$logline = '['.date('D M d H:i:s Y').'] '.$logline;
			break;
		// Apache1.3 doesn't add the client IP address
		case 'apache':
			$logline = '[client '.$_SERVER['REMOTE_ADDR'].'] '.$logline;
			break;
		}
		return $logline;
	}
	
	
	
	/**
	 * Log an error string.
	 * @param string line to log
	 * @param string|resource optional log file or stream resource to send output
	 * @return mixed path logged to, true indicates successful non-file logging, or false on error
	 */	
	static function log( $logline, $out = '' ) {
		// Output log line, no error checking to save performance
		// Send to descriptor or other stream if resource passed
		if( is_resource($out) && fwrite( $out, "$logline\n" ) ){
			return true;
		}
		// Log to specified file
		else if( $out && error_log( "$logline\n", 3, $out ) ){
			return $out;
		}
		// else default - probably apache error log
		else if( error_log( $logline, 0 ) ){
			$out = ini_get('error_log');
			return $out ? $out : true;
		}
		else {
			return false;
		}
	}
	
	
	
	
	/**
	 * @internal
	 * @return string
	 */	
	function __toString() {
		return call_user_func( self::$displayfunc, $this );
	}
	
	
	
	/**
	 * Default error formatting function
	 * @param PLUGError
	 * @return string
	 */	
	static function display( PLUGError $Err ){
		$html = ini_get('html_errors');
		if( $html ){
			$s = '<div class="error"><strong>%s:</strong> %s. in <strong>%s</strong> on line <strong>%u</strong></div>';
		}
		else {
			$s = "\n%s: %s. in %s on line %u";
		}
		$args = array ( 
			$s,
			$Err->getTypeString(),
			$Err->getMessage(), 
			$Err->getFile(),
			$Err->getLine()
		);
		// add trace in dev mode
		if( ! PLUG::is_compiled() ){
			$args[0] .= $html ? "\n<pre>%s</pre>" : "\n%s";
			$args[] = $Err->getTraceAsString();
		}
		return call_user_func_array( 'sprintf', $args );
	}
	
	
	
	
	/**
	 * Clear this error
	 * @return Void
	 */	
	public function clear() {
		unset( self::$stack[ $this->type ][ $this->id ] );
		if( empty( self::$stack[ $this->type ] ) ){
			unset( self::$stack[ $this->type ] );
		}
		$this->id = null;
	}	
	
	
	
	
	/**
	 * Get all raised errors of specific types
	 * @param int optional type bitmask
	 * @return array
	 */
	static function get_errors( $emask = null ) {
		$all = array();
		foreach( self::$stack as $type => $errs ){
			if( $emask === null || $type & $emask ) {
				foreach( $errs as $Err ){
					$all[] = $Err;
				}
			}
		}
		return $all;
	}	




	/**
	 * Get a reference to an error instance.
	 * @internal
	 * @param int
	 * @param int
	 * @return PLUGError
	 */ 
	static function &get_reference( $type, $id ) {
		if( empty( self::$stack[ $type ][ $id ] ) ){
			$null = null;
			return $null;
		}	
		else {
			return self::$stack[ $type ][ $id ];
		}
	}
	
	

	
	/**
	 * Test whether errors of a certain type have been raised system wide
	 * @param int optional bitmask
	 * @return bool
	 */	
	static function is_error( $emask = null ) {
		if( $emask === null ){
			return (bool) self::get_global_level();
		}	
		else {
			return (bool) ( self::get_global_level() & $emask );
		}
	}
	
	
	
	
	/**
	 * get system wide level of errors
	 * @return int
	 */
	static function get_global_level () {
		$e = 0;
		$types = array_keys( self::$stack );
		foreach( $types as $t ) {
			$e |= $t;
		}
		return $e;
	}
	
	
	
	
}

