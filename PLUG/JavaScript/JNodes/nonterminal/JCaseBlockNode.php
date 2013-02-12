<?php
/**
 * File containing class JCaseBlockNode
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JCaseBlockNode.php,v 1.1 2009/03/22 12:51:08 twhitlock Exp $
 */
 
 
/**
 * JavaScript node class for the J_CASE_BLOCK symbol
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JCaseBlockNode extends JNodeBase {

	/**
	 * @override
	 */
	function format_lines( &$line, array &$lines ){
		foreach( $this->children as $Node ){
			if( $Node->is_symbol('{') ){
				JNodeBase::format_newline('{', $line, $lines );
			}
			else if( $Node->is_symbol('}') ){
				if( $line ){
					JNodeBase::format_newline('', $line, $lines );
				}
				JNodeBase::format_newline('}', $line, $lines );
			}
			else {
				// ident case blocks
				$blocklines = array();
				$Node->format_lines( $line, $blocklines );
				foreach( $blocklines as $blockline ){
					$lines[] = "\t".$blockline;
				}
			}
		}
	}

}
