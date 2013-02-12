<?php
/**
 * File containing PLUG.session.PLUGCliSession
 * @author Tim Whitlock
 * @category PLUG
 * @package session
 * @version $Id: PLUGCliSession.php,v 1.1 2009/03/22 12:57:32 twhitlock Exp $
 */

 

/**
 * Require session base class
 */ 
import('PLUG.session.PLUGSession');

 
/**
 * PLUG Cli Session class.
 * Provides a pseudo session handling interface for command line programs
 * @category PLUG
 * @package session
 */
class PLUGCliSession extends PLUGSession {




	/**
	 * @override
	 */
	static function start( $sessid = null ){
		// by default, maintain a session per shell client
		if( is_null($sessid) ){
			$sessid = trim(`whoami`);
			if( isset($_SERVER['SHELL']) ){
				$sessid .= $_SERVER['SHELL'];
			}
			if( isset($_SERVER['SSH_CLIENT']) ){
				$sessid .= '-'.$_SERVER['SSH_CLIENT'];
			}	
			$sessid = preg_replace('/\W/', '-', $sessid );
		}	
		return PLUGSession::start( $sessid );	
	}

	





} 
