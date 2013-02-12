<?php
/**
 * File containing class JBlockNode
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JBlockNode.php,v 1.1 2009/03/22 12:51:08 twhitlock Exp $
 */
 
 
/**
 * JavaScript node class for the J_BLOCK symbol
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JBlockNode extends JNodeBase {

	
	
	
	
	/**
	 * @override
	 */
	function format_lines( &$line, array &$lines ){
		$Block = $this->get_child(1);
		if( ! $Block->is_symbol(J_STATEMENT_LIST) ){
			JNodeBase::format_newline('{ }', $line, $lines );
			return;
		}
		// else collect block statement
		JNodeBase::format_newline('{', $line, $lines );
		$blocklines = array();
		$Block->format_lines( $line, $blocklines );
		// indent block
		foreach( $blocklines as $blockline ){
			$lines[] = "\t".$blockline;
		}
		if( $line ){
			JNodeBase::format_newline('', $line, $lines );
		}
		JNodeBase::format_newline('}', $line, $lines );
	}



}
