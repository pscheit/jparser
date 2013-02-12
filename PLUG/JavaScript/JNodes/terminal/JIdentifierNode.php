<?php
/**
 * File containing class JIdentifierNode
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JIdentifierNode.php,v 1.1 2009/03/22 12:51:09 twhitlock Exp $
 */
 
 
/**
 * JavaScript node class for the J_IDENTIFIER symbol
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JIdentifierNode extends JNodeBase {

	
	/**
	 * Map of identifiers to obfuscated names
	 * @var array
	 */
	private static $obfMap;
	
	/**
	 * Incremental value for obfuscation of names
	 * @var int
	 */
	private static $obfInc = 0;
	
	/*
	 * list if protected names that will not be obfuscated
	 * @ignore
	 * @var array
	 *
	protected static $obfSys = array (
		'prototype' => '',
	    '__proto__' => ''
	);	
	//*/
	
	/**
	 * Original identifier if obfuscated
	 * @var string
	 */
	private $obfuscated;
	
	
	
	/**
	 * @param string
	 * @return void
	 */
	function __obfuscate( array &$names ){
		if( isset($this->obfuscated) ){
			return $this->value;
		}
		if( ! isset($names[$this->value]) ){
			//$names[$this->value] = '$'.$this->value;
			$names[$this->value] = self::obf_name($this->value);
		}
		$this->obfuscated = $this->value;
		return $this->value = $names[$this->value];
	}
	
	
	
	/**
	 * @override
	 */
	function obfuscate( array &$names ){
		if( isset($names[$this->value]) ){
			$this->value = $names[$this->value];
		}
	}
	
	
	
	/**
	 * Get (or make) an obfuscated identifer name
	 * @param string clear text name
	 * @return string obfuscated name
	 */
	static function obf_name( $name ){
		//if( preg_match('/^\$\d+$/', $name ) ){
		//	// already obfuscated
		//	return $name;
		//}
		if( ! isset(self::$obfMap[$name]) ){
			//if( isset(self::$obfSys[$name]) ){
			//	return self::$obfSys[$name] ? self::$obfSys[$name] : $name;
			//}
			self::$obfMap[$name] = sprintf( '$%x', ++self::$obfInc );
		}
		return self::$obfMap[$name];
	}
	

}
