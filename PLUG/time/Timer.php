<?php

namespace PLUG\time;

/**
 * File containing PLUG.time.Timer class
 * @category PLUG
 * @package time
 * @date 11 December 2004
 * @version $Id: Timer.php,v 1.1 2009/03/22 12:58:02 twhitlock Exp $
 */

 
/**
 * Timer Object.
 * Mainly for debugging and bench-test purposes
 * @example time/Timer.php
 * @category PLUG
 * @package time
 */
class Timer {

	/**
	 * microtime zero point.
	 * @var string
	 */
	private $started = null;
	
	/**
	 * microtime stopped.
	 * @var string
	 */
	private $stopped = null;
	
	
	/**
	 * Constructor.
	 * @param string optional microtime to force start time.
	 */
	function __construct( $microtime =  '' ){
		if( is_float($microtime) ){
			trigger_error('Timer class is designed for use with more accurate string microtimes', E_USER_ERROR );
		}
		else if( $microtime ){
			$this->started = $microtime;
		}
		else {
			$this->started = microtime( false );
		}
	}
	
	
	
	/**
	 * Reset timer.
	 * @return int milliseconds timer was last stopped or 0.
	 */
	 function reset(){
		$tmp = $this->milliseconds();
	 	$this->started = microtime( false );
		$this->stopped = null;
		return $tmp;
	 }
	 
	 
	 
	 
	 /**
	  * Stop timer.
	  * Use this when you want to evaluate a value without the clock continuing.
	  * @return int milliseconds or False if timer already has been stopped.
	  */
	 function stop(){
		if( $this->is_running() ){
			$this->stopped = microtime( false );
			return $this->milliseconds();
		}
		else{
			trigger_error("Timer already stopped", E_USER_NOTICE);
			return false;
		}
	 }
	 
	 
	 
	 
	 /**
	  * Test whether timer is running
	  * @return bool
	  */
	 function is_running(){
	 	if( isset( $this->stopped ) ){
			return false;
		}
		if( ! isset( $this->started ) ){
			trigger_error("Timer has been stopped some how (".gettype($this->started).")'$this->started'", E_USER_WARNING);
			return false;
		}
		return true;
	 }
	 
	 
	 
	 
	 
	 /**
	  * Get milliseconds since timer started.
	  * @return float milliseconds
	  */
	 function milliseconds( $dp = null ){
	 	if( $this->is_running() ){
		 	$now = microtime( false );
		}
		else {
			$now = $this->stopped;
		}
		$started = self::parse_microtime( $this->started );
		$stopped = self::parse_microtime( $now );
		$ms = $stopped - $started;
		if( ! is_null($dp) ){
			$mult = pow( 10, $dp );
			$ms = round( $mult * $ms ) / $mult;
		}
		return $ms;
	 }
	 
	 
	 
	 
	 /**
	  * Get seconds since timer started.
	  * @param int precision; defaults to two decimal places
	  * @return float seconds to specified precision
	  */
	 function seconds( $dp = 2 ){
		$secs = $this->milliseconds() / 1000;
		if( ! is_null($dp) ){
			$mult = pow( 10, $dp );
			$secs = round( $mult * $secs ) / $mult;
		}
		return $secs;
	 }
	
	
	
	
	
	
	/**
	 * Parses string microtime into milliseconds.
	 * @param string microtime as returned by microtime( false )
	 * @return float milliseconds
	 */
	static function parse_microtime( $microtime ){
		list($usec, $sec) = explode( ' ', $microtime ); 
		$ms1 = (float) $usec * 1000;
		$ms2 = (float) $sec  * 1000;
		return $ms1 + $ms2;
	}







}