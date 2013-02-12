<?php
/**
 * File containing PLUG.session.PLUGSessionObject class
 * @author Tim Whitlock
 * @category PLUG
 * @package core
 * @version $Id: PLUGSessionObject.php,v 1.1 2009/03/22 12:57:32 twhitlock Exp $
 */



/**
 * Inherit base object
 */ 
import('PLUG.core.PLUGObject');

 
 
/**
 * Generic object super class for serializing in session
 * @category PLUG
 * @package core
 * @version $Id: PLUGSessionObject.php,v 1.1 2009/03/22 12:57:32 twhitlock Exp $
 */
class PLUGSessionObject extends PLUGObject {

	/**
	 * Object's `time to live' when serialized in session.
	 * @var int
	 */
	public $sess_ttl;	
	
	/**
	 * @ignore
	 * Whether to freeze sess_ttl when class definition is not available between scripts, ( hibernating ).
	 * @var bool
	 */
	public $sess_hibernate = true;


	
	
	// --- LIFESPAN AND HIBERNATION FUNCTIONS ----------
	// -------------------------------------------------
	


	/**
	 * Define how many times object can be deserialized from session before it is dead
	 * @param int objects time-to-live
	 * @param bool optional hibernation flag, freezes lifespan when class definition not available
	 * @return int previous lifespan
	 */	
	function session_lifespan( $ttl = null, $hibernate = null ){
		$current = $this->sess_ttl;
		if( $ttl !== null ){
			$this->sess_ttl = $ttl;
		}
		if( $hibernate !== null ){
			$this->sess_hibernate = $hibernate;
		}
		return $current;
	}
	
	
	
	
	/**
	 * @ignore
	 * Define whether this lifespan is frozen when class definition is not available.
	 * @param bool hibernation flag, freezes lifespan when class definition not available
	 * @return previous value
	 *
	function session_hibernate( $hibernate = null ){
		$current = $this->sess_hibernate;
		if( $hibernate !== null ){
			$this->sess_hibernate = $hibernate;
		}
		return $current;
	}
	*/
	


}







?>