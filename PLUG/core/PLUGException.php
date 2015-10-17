<?php

namespace PLUG\core;

/**
 * File containing PLUG.core.PLUGException class
 * @category PLUG
 * @package core
 */

use Exception;

/**
 * Standard exception error code
 */
define( 'PLUG_EXCEPTION_STD', -2 );

 
 
/**
 * Standard PLUG Exception
 * @category PLUG
 * @package core
 */
class PLUGException extends Exception {

	/**
 	 * Constructor
 	 */
	public function __construct( $message, $code = PLUG_EXCEPTION_STD ) {
		Exception::__construct( $message, $code );
	}


}

