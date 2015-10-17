<?php

namespace PLUG\JavaScript\JNodes\nonterminal;

/**
 * File containing class JIterStatementNode
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JIterStatementNode.php,v 1.1 2009/03/22 12:51:09 twhitlock Exp $
 */

use PLUG\JavaScript\JNodes\JNodeBase;

/**
 * JavaScript node class for the J_ITER_STATEMENT symbol
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JIterStatementNode extends JNodeBase {

	
	
	/**
	 * @override
	 * ensure no breaks after ";"s
	 */
	function format_lines( &$line, array &$lines ){
		foreach( $this->children as $Node ){
			if( $Node->is_symbol(';') ){
				$line = rtrim( $line, ' ' );
				$line .= '; ';
			}
			else {
				$Node->format_lines( $line, $lines );
			}
		}
	}
	

}
