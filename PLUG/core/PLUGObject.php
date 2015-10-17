<?php

namespace PLUG\core;

/**
 * File containing PLUG.core.PLUGObject class
 * @author Tim Whitlock
 * @category PLUG
 * @package core
 * @version $Id: PLUGObject.php,v 1.1 2009/03/22 12:42:21 twhitlock Exp $
 */
 
 
/**
 * Generic object super class.
 * Provides common object level functionality, ( e.g error handling )
 * @example core/PLUGObject.php
 * @category PLUG
 * @package core
 */
class PLUGObject {

	/**
	 * Stack of all raised errors
	 * @var array
	 */
	private $err_stack = array();

	
	// --- ERROR HANDLING FUNCTIONS ---------------------
	// --------------------------------------------------
	
	
	
	
	/**
	 * Raise a PLUGError originating from this instance.
	 * @param int error code.
	 * @param string error message text
	 * @param int error type constant
	 * @return void
	 */	
	protected function trigger_error( $code, $message, $type = E_USER_NOTICE ){
		$trace = debug_backtrace();
		$Err = PLUG::raise_error( $code, $message, $type, $trace );
		$this->on_trigger_error( $Err );
	}
	
	
	
	
	/**
	 * @internal
	 * @param PLUGError reference to raised error
	 * @return void
	 */	
	function on_trigger_error( PLUGError $Err ) {
		$t = $Err->getType();
		$i = $Err->getId();
		$this->err_stack[ $t ][] = $i;
	}




	/**
	 * Get current level of errors raised in this object.
	 * @return int
	 */
	function get_error_level() {
		// calculate level in case global errors have been cleared
		$e = 0;
		foreach( $this->err_stack as $t => $ids ) {
			foreach( $ids as $id ){
				$Err = PLUGError::get_reference( $t, $id );
				if( is_object($Err) ){
					$e |= $t;
					break;
				}
			}
		}
		return $e;
	}



	
	/**
	 * Get errors raised in this object.
	 * @param int php error level constant
	 * @return array
	 */
	function get_errors( $emask = null ) {
		$errs = array();
		foreach( $this->err_stack as $t => $ids ) {
			if( $emask !== NULL && ( $emask & $t ) == 0 ) {
				// ignore this error
				continue;
			}
			// collect these errors
			foreach( $ids as $id ){
				$Err = PLUGError::get_reference( $t, $id );
				if( is_object($Err) ){
					$errs[] = $Err;
				}
			}
		}
		return $errs;
	}	
	
	
	
	
	/**
	 * Clear errors raised in this object.
	 * @param int php error level constant
	 * @return array
	 */
	function clear_errors( $emask = null ) {
		foreach( $this->err_stack as $t => $ids ) {
			if( $emask !== NULL && ( $emask & $t ) == 0 ) {
				// ignore this error
				continue;
			}
			// clear these errors
			foreach( $ids as $id ){
				$Err = PLUGError::get_reference( $t, $id );
				$Err->clear();
			}
			unset( $this->err_stack[$t] );
		}
	}	

	
	
	
	/**
	 * Dump errors raised in this object.
	 * @param int php error level constant
	 * @return array
	 */
	function dump_errors( $emask = null ) {
		foreach( $this->err_stack as $t => $ids ) {
			if( $emask !== NULL && ( $emask & $t ) == 0 ) {
				// ignore this error
				continue;
			}
			// dump these errors
			foreach( $ids as $id ){
				$Err = PLUGError::get_reference( $t, $id );
				echo (string) $Err;
				echo $Err->getTraceAsString(), "\n";
			}
		}
	}



	
	/**
	 * Test whether this object is in a state of error
	 * @param int php error level constant
	 * @return bool
	 */	
	function is_error( $emask = null ) {
		$e = $this->get_error_level();
		if( $emask === null ){
			return (bool) $e;
		}	
		else {
			return (bool) ( $e & $emask );
		}
	}
	
	


}







?>