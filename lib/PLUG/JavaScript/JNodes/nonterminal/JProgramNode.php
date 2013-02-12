<?php
/**
 * File containing class JProgramNode
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JProgramNode.php,v 1.1 2009/03/22 12:51:09 twhitlock Exp $
 */
 
 
/**
 * JavaScript node class for the J_PROGRAM start symbol
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JProgramNode extends JNodeBase {


	
	/**
	 * Main entry for obfuscating parse tree
	 * @var array list of obfuscated names in current scope
	 * @return void
	 */
	function obfuscate( array &$names ){
		$Elements = $this->reset();
		$Elements->obfuscate( $names );
	}
	
	
	
	/**
	 */	
	function format(){	
		$Elements = $this->reset();
		$line = '';
		$lines = array();
		$Elements->format_lines( $line, $lines );
		return implode("\n", $lines );
	}
	
	

}
