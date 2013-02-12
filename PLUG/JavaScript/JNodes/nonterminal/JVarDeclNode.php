<?php
/**
 * File containing class JVarDeclNode
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JVarDeclNode.php,v 1.1 2009/03/22 12:51:09 twhitlock Exp $
 */
 
 
/**
 * JavaScript node class for the J_VAR_DECL symbol
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JVarDeclNode extends JNodeBase {

	
	
	/**
	 * 
	 */
	function obfuscate( array &$names ){
		$Identifier = $this->reset();
		$Identifier->__obfuscate( $names );
		// initializer is optional
		$Initializer = $this->next();
		if( $Initializer ){
			$Initializer->obfuscate( $names );
		}
	}
	
	
	

}
