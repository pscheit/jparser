<?php

namespace PLUG\JavaScript\JNodes\nonterminal;

/**
 * File containing class JElementsNode
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JElementsNode.php,v 1.1 2009/03/22 12:51:08 twhitlock Exp $
 */

use PLUG\JavaScript\JNodes\JNodeBase;
use PLUG\JavaScript\JNodes\terminal\JIdentifierNode;

/**
 * JavaScript node class for the J_ELEMENTS symbol
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JElementsNode extends JNodeBase {

	
	/**
	 * @override
	 */
	function obfuscate( array &$names ){
		// Obfuscate all function names first, in case a function is called before it is defined
		$Funcs = $this->get_nodes_by_symbol( J_FUNC_DECL, 1 );
		foreach( $Funcs as $Func ){
			// Function declaration always has identifier as second child
			$Identifier = $Func->get_child(1);
			if( $Identifier instanceof JIdentifierNode ){
				$Identifier->__obfuscate( $names ); 
			}
		}
		// continue as per default
		parent::obfuscate( $names );
		// now we can recurse with function names in scope
		// careful with &refs as we need to isolate the name spaces
		foreach( $Funcs as $i => $Func ){
			$scope = $names;
			$Func->obfuscate( $scope );
		}
		// Handle function Expressions which could be at any depth
		foreach( $this->get_nodes_by_symbol(J_FUNC_EXPR) as $Func ){
			$scope = $names;
			$Func->obfuscate( $scope );
		}		
	}
		
	
}
