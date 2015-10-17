<?php

namespace PLUG\JavaScript\JNodes\nonterminal;

/**
 * File containing class JCaseDefaultNode
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JCaseDefaultNode.php,v 1.1 2009/03/22 12:51:08 twhitlock Exp $
 */

use PLUG\JavaScript\JNodes\JNodeBase;

/**
 * JavaScript node class for the J_CASE_DEFAULT symbol
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JCaseDefaultNode extends JNodeBase {

	/**
	 * @override
	 */
	function format_lines( &$line, array &$lines ){
		foreach( $this->children as $Node ){
			if( $Node->is_symbol(':') ){
				$line = rtrim( $line, ' ' );
				JNodeBase::format_newline(':', $line, $lines );
			}
			else {
				$caselines = array();
				$Node->format_lines( $line, $caselines );
				foreach( $caselines as $caseline ){
					$lines[] = "\t".$caseline;
				}
			}
		}
	}

}
