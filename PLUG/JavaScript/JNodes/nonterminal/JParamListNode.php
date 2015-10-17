<?php

namespace PLUG\JavaScript\JNodes\nonterminal;

/**
 * File containing class JParamListNode
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JParamListNode.php,v 1.1 2009/03/22 12:51:09 twhitlock Exp $
 */

use PLUG\JavaScript\JNodes\JNodeBase;

/**
 * JavaScript node class for the J_PARAM_LIST symbol
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JParamListNode extends JNodeBase {

	


	
	/**
	 * @override
	 */
	function obfuscate( array &$names ){
		foreach( $this->get_nodes_by_symbol( J_IDENTIFIER, 1 ) as $Identifier ){
			$Identifier->__obfuscate( $names );
		}
	}
	
	
	/**
	 * @override
	 *
	function format_lines( &$line, array &$lines ){
		foreach( $this->get_nodes_by_symbol( J_IDENTIFIER, 1 ) as $Identifier ){
			$params[] = $Identifier->__toString();
		}
		$line .= implode(', ',$params).' ';
	}
	*/
	
	


}
