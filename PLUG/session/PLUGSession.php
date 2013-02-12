<?php
/**
 * File containing PLUG.session.PLUGSession
 * @author Tim Whitlock
 * @category PLUG
 * @package session
 * @subpackage 
 * @version $Id: PLUGSession.php,v 1.1 2009/03/22 12:57:32 twhitlock Exp $
 */

 

/**
 * Require session object base class
 */ 
import('PLUG.session.PLUGSessionObject');



/* 
 * Standard session cookie setup.
 * ensure your ini settings look like this.
ini_set('session.use_cookies', true);
ini_set('session.use_only_cookies', true);
ini_set('session.cache_limiter', 'nocache');
ini_set('session.cookie_lifetime', 0);
*/

 
 
/**
 * PLUG Session class.
 * Provides session handling interface
 * @example session/PLUGSession.php
 * @todo listeners for session timeout
 * @category PLUG
 * @package session
 */
class PLUGSession {

	/**
	 * Session data
	 * @var array
	 */	
	private $Data = array( );

	/**
	 * Session id
	 * @var string
	 */	
	private $id;

	/**
	 * Original session id that had timed out
	 * @var string
	 */	
	private $expired_id;

	/**
	 * Timestamp session was started
	 * @var int
	 */
	private $t_started;
	
	/**
	 * Timestamp of last time session was resumed
	 * @var int
	 */
	private $t_resume;
	
	/**
	 * Number of seconds session has slept for 
	 * @var int
	 */
	private $t_idle = 0;
	
	/**
	 * Singleton
	 */
	private static $Session;		
	
	
	/**
	 * Constructor
	 * @internal
	 */
	private function __construct() {
		self::$Session = $this;
		$this->t_resume = $this->t_started = time();
		$this->id = session_id();
		$_SESSION['PLUGSession'] = $this;
		self::header('new');
	}	

	
	
	
	/**
	 * Destructor
	 * @ignore
	 * @return Void
	 */
	function __destruct() {
		// Shutdown the session.
		foreach( array_keys($this->Data) as $v ){
			if( ! self::sleep_recursive( $this->Data[$v] ) ){
				// this branch will not be saved in session.
				unset( $this->Data[$v] );
			}
		}
	}




	/**
	  * Recursive utility function; called by {@link PLUGSession::__destruct()}
	  * Dead objects are removed from the tree.
	  * @static
	  * @access private
	  * @param Mixed any node in session tree
	  * @return bool whether branch should be seralized
	  */
	 private static function sleep_recursive( &$Obj ){
	 	
		// always serialize scalar values
		if( is_scalar( $Obj ) ){
			return true;
		}
	 	
		if( is_array($Obj) ){
			foreach( array_keys($Obj) as $i ){
				if( !self::sleep_recursive($Obj[$i]) ){
					// remove from branch
					unset( $Obj[$i] );
				}
			}
			if( empty($Obj) ){
				// don't serialize this branch, it is empty
				// will keep session tidier
				return false;
			}
			else {
				return true;
			}
		}
		
		// check / decrement PLUGSessionObject lifespan
		if( $Obj instanceof PLUGSessionObject ){
			$ttl = $Obj->session_lifespan();
			if( $ttl === null ){
				// serialize object as it has no ttl set 
				return true;
			}
			if( $ttl === 0 ){
				// Object has died, do not serialize
				return false;
			}
			// serialize, but decrement lifespan
			$Obj->session_lifespan( --$ttl );
			return true;
		}

		// decrement lifespan in incomplete object
		// we cannot alter properties of incomplete object.. massive hack needed
		//                                                   -------------------
		if( $Obj instanceof __PHP_Incomplete_Class ){
			$vars = get_object_vars( $Obj );
			if( ! empty($vars['sess_hibernate']) ){
				// object may sleep until class available again.
				return true;
			}
			if( ! isset($vars['sess_ttl']) ){
				// serialize object as it has no ttl set 
				return true;
			}
			$oldttl = $vars['sess_ttl'];
			if( $oldttl === 0 ){
				// Object has died, do not serialize
				return false;
			}
			$newttl = max( 0, $oldttl - 1 );
			// hacking the serialized string is the only way to alter incomplete object's sess_ttl property
			$Obj = unserialize( str_replace( "sess_ttl\";i:$oldttl", "sess_ttl\";i:$newttl", serialize($Obj) ) );
			return true;
		}
		// serialize anything else
		return true;
	 }

	
	
	
	/**
	 * Magic wakeup function.
	 * When this is executed the session is not fully initialized, 
	 * so we will validate the woken session in {@link PLUGSession::start}
	 * @internal
	 * @return Void
	 */	
	private function __wakeup() {
		self::$Session = $this;
		$tnow = time();
		$this->t_idle = $tnow - $this->t_resume;
		$this->t_resume = $tnow;
	}
	
	
	
	/**
	 * Magic sleep function.
	 * @internal
	 * @return array
	 */	
	public function __sleep() {
		return array( 'id', 't_started', 't_resume', 'Data' );
	}
	
	
	

	/**
	 * Start session
	 * @param string optional session id to force
	 * @return bool
	 */	
	static function start( $sessid = null ) {
		// switch to named session
		$oldname = session_name( PLUG_SESSION_NAME );
		// session could have been started already
		if( session_id() ){
			if( $oldname === PLUG_SESSION_NAME ){
				trigger_error( "Session '$oldname' already started", E_USER_NOTICE );
			}
			// or another system could have started a session under a different name
			else if( ! session_destroy() ){
				trigger_error( "Failed to destroy session '$oldname'", E_USER_WARNING );
				return false;
			}
			// force session id if passed
			if( $sessid ){
				session_id( $sessid );
			}
			if( ! session_start() ){
				trigger_error( "Failed to start session after destroying previous session '$oldname'", E_USER_WARNING );
				return false;
			}
		}
		// else ok to start session for the first time
		// when session starts __wakeup will be called if resuming
		else {
			// force session id if passed
			if( $sessid ){
				session_id( $sessid );
			}
			if( ! session_start() ){
				trigger_error( 'Failed to start session', E_USER_WARNING );
				return false;
			}
		}
		// create new instance of self if new session
		if( ! isset(self::$Session) ){
			new PLUGSession();
		}
		// manually check timeout now that session is intilaized
		// Session must expire now regardless of cookie lifetime.
		else if( PLUG_SESSION_TIMEOUT && self::$Session->t_idle >= (PLUG_SESSION_TIMEOUT * 60) ){
			$id = session_id();
			self::destroy();
			self::$Session->expired_id = $id;
		}
		// else session resumed
		else {
			self::header('resume-after-'.self::$Session->t_idle.'-seconds');
		}
		return true;
	}	
	
	
	
	/**
	 * Destroy current session
	 * @return void
	 */	
	static function destroy(){
		if( ! self::started() ){
			trigger_error( "Session not started", E_USER_WARNING );
			return;
		}
		if( session_id() !== self::$Session->id ){
			trigger_error( "Wrong session id", E_USER_WARNING );
			return;
		}
		// start new session trashing the old one
		if( ! headers_sent( $file, $line ) ){
			session_regenerate_id( true );
			new PLUGSession();
		}
		// unless it's too late to send a new cookie
		else {			
			session_destroy();
			unset( $_SESSION['PLUGSession'] );
		}
	}
	
		
	
	/**
	 * Send an X-* header for debugging purposes
	 * @param string header content text
	 * @return void
	 */
	private static function header( $h ){
		if( ! PLUG_CLI && ! headers_sent() ){
			header( 'X-PLUG-Session: '.$h, true );
		}
	}
		
	
	
	/**
	 * Test whether session has started
	 * @return bool
	 */	
	static function started() {
		return session_id() && isset( self::$Session );
	}	


	
	/**
	 * Test whether session is new, as opposed to resumed
	 * @return bool
	 */	
	static function is_new(){
		return self::$Session->t_started === self::$Session->t_resume;
	}	


	
	/**
	 * Test whether the original session expired when it was woken up
	 * @return bool
	 */	
	static function had_expired(){
		return isset( self::$Session->expired_id );
	}	

	

	/**
	 * Retrieve value from session.
	 * Avoids direct use of $_SESSION global.
	 * @param array
	 * @return mixed
	 */
	static function get_value( $key ) {
		if( ! self::started() ){
			trigger_error("Session not started, cannot get_value", E_USER_WARNING );
			return null;
		}
		if( !is_array($key) ){
			$key = explode( '.', $key );
		}
		$Obj = self::$Session->Data;
		foreach( $key as $k ){
			if( !isset( $Obj[$k] ) ){
				return null;
			}
			$Obj = $Obj[$k];
		}
		return $Obj;
	} 	

	
	
	
	/**
	 * Set value in session.
	 * Avoids direct use of $_SESSION global.
	 * @param array
	 * @param mixed
	 * @return bool whether value was set
	 */
	static function set_value( $key, $value ) {
		if( ! self::started() ){
			trigger_error("Session not started, cannot set_value", E_USER_WARNING );
			return null;
		}
		if( ! is_array($key) ){
			$key = explode( '.', $key );
		}
		$Obj =& self::$Session->Data;
		$prop = array_pop( $key );
		foreach( $key as $k ){
			if( !isset( $Obj[$k] ) ){
				$Obj[$k] = array( );
			}
			else if( ! is_array($Obj[$k]) ){
				trigger_error("Conflict on session key `".implode('.', $key)."'", E_USER_WARNING );
				return false;
			}
			$Obj = &$Obj[$k];
		}
		// set value at this point
		$Obj[$prop] = $value;
		return true;
	} 	

} 
