<?php
/**
 * File containing class JLabelledStatementNode
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JLabelledStatementNode.php,v 1.1 2009/03/22 12:51:09 twhitlock Exp $
 */
 
 
/**
 * JavaScript node class for the J_LABELLED_STATEMENT symbol
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JLabelledStatementNode extends JNodeBase {

	
	
	/**
	 * @override
	 */
	function obfuscate( array &$names ){
		// Our identifier may be obfuscated, but is not available outside this scope
		// We must unset it afterwards unless it is already in scope as another identifier
		$Identifier = $this->reset();
		$label = $Identifier->__toString();
		$inscope = isset( $names[$label] );
		$Identifier->__obfuscate( $names );
		// drill down, and unset 
		$Statement = $this->get_child( 2 );
		$Statement->obfuscate( $names );
		if( ! $inscope ){
			unset( $names[$label] );
		}
	}

	
	
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
				$Node->format_lines( $line, $lines );
			}
		}
	}	

}
