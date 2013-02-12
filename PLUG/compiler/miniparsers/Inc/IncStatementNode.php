<?php
/**
 * File containing class IncStatementNode
 * @author Tim Whitlock
 * @category PLUG
 * @package compiler
 * @subpackage miniparsers
 * @version $Id: IncStatementNode.php,v 1.1 2009/03/22 12:58:58 twhitlock Exp $
 */

import('PLUG.parsing.LR.LRParseNode');
 
 
/**
 * Root node for include statement miniparser.
 * @category PLUG
 * @package compiler
 * @subpackage miniparsers
 */
class IncStatementNode extends LRParseNode {



	/**
	 * Fetch full resolved arguments.
	 * @param array registry of contants values to override those currently defined in script
	 * @param array registry of variables, e.g. <code>array ( 'argv' => array('path/info'), '_SERVER' => array( ... ), ... );</code>
	 * @return array one or more string arguments
	 */
	function get_args( array $consts, array $vars ){
		$args = array();
		// limit depth of search to avoid collecting nested func calls
		$argnodes = $this->get_nodes_by_symbol( NT_ARG, 3 );
		foreach( $argnodes as $i => $arg ){
			$args[] = $arg->compile_string( $consts, $vars );
		}
		return $args;
	}



}
	
 
 
