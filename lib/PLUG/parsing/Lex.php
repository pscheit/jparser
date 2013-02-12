<?php
/**
 * File containing class Lex
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @version $Id: Lex.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */


/**
 * Special "empty string" symbol common to any Lex.
 */ 
define('P_EPSILON', -1 );

/**
 * Special "end of file" symbol common to any Lex.
 */ 
define('P_EOF', -2 );

/**
 * Special "goal" symbol common to any Lex.
 */ 
define('P_GOAL', -3 );
 
 
 

/**
 * Lexicon base class
 * - This is a registry of the permitted symbols in an alphabet, with related compiling and debugging tools.
 * - It has no knowledge of grammar rules, or whether symbols are terminal or non-terminal, see {@link Grammar} class for this.
 * - You can build a Lex at runtime or pre-compile it into a subclass for more efficient useage. see {@link class_export}.
 * @category PLUG
 * @package parsing
 */
abstract class Lex {
	
	/**
	 * Singleton registry
	 * @var array
	 */
	private static $singletons = array();
	
	/**
	 * Internal incremental value for new constant definitions
	 * @var int
	 */
	protected $i;
	
	/**
	 * Internal list of registered symbol names indexed by their constant value
	 * @var array
	 */
	protected $names = array (
		P_EPSILON => 'P_EPSILON',
		P_EOF     => 'P_EOF',
		P_GOAL    => 'P_GOAL',
	);
	
	/**
	 * Internal list of literal symbols indexed by length;
	 * i.e. symbols that don't require naming. e.g. [ ";" => ";" ]
	 * @var array
	 */
	protected $literals = array();

	

	/**
	 * Constructor
	 * @param int Lowest value for new token constant definitions, defaults to 0
	 */	
	function __construct( $i = null ){
		if( ! is_null($i) ){
			$this->i = (int) $i;
		}
	}
	
	
	/**
	 * Explicit destructor
	 * @return void
	 */
	function destroy(){
		$class = get_class( $this );
		unset( self::$singletons[$class] );
		unset( $this->names );
		unset( $this->literals );
	}
	
	
	/**
	 * Factory method
	 * @param string Lex subclass
	 */
	static function get( $class ){
		if( ! isset(self::$singletons[$class]) ){
			self::$singletons[$class] = new $class;
		}
		return self::$singletons[$class];
	}	
		
	

	/**
	 * Test if symbol is defined in this Lex
	 * @param string 
	 * @return bool
	 */
	function defined( $c ){
		if( isset($this->literals[$c]) ){
			return true;
		}
		if( ! defined($c) ){
			return false;
		}
		$i = constant( $c );
		return isset( $this->names[$i] ) && $this->names[$i] === $c;
	}
	
	
	
	/**
	 * Get internal name from constant symbol value
	 * @param int
	 * @return string
	 */	
	function name( $i ){
		if( is_int($i) ){
			if( ! isset($this->names[$i]) ){
				trigger_error("symbol ".var_export($i,1)." is unknown in ".get_class($this), E_USER_NOTICE );
				return 'UNKNOWN';
			}
			else {
				return $this->names[$i];
			}
		}
		else if ( ! isset($this->literals[$i]) ){
			trigger_error("literal symbol ".var_export($i,1)." is unknown in ".get_class($this), E_USER_NOTICE );
		}
		return $i;
	}
	
	
	
	/**
	 * @param string
	 * @param array
	 * @return string
	 */	
	function implode( $s, array $a ){
		$b = array();
		foreach( $a as $t ){
			$b[] = $this->name( $t );
		}
		return implode( $s, $b );
	}	
	
	
	
	/**
	 * Debugging function
	 * @return void
	 */	
	function dump(){
		asort( $this->names, SORT_STRING );
		$t = max( 2, strlen( (string) $this->i ) );
		foreach( $this->names as $i => $n ){
			$i = str_pad( $i, $t, ' ', STR_PAD_LEFT );
			echo "$i => $n \n";
		}
	}


	
	
	
}

