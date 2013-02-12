<?php
/**
 * File containing class JMemberExprNode
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JMemberExprNode.php,v 1.1 2009/03/22 12:51:09 twhitlock Exp $
 */
 
 
/**
 * JavaScript node class for the J_MEMBER_EXPR symbol
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JMemberExprNode extends JNodeBase {

	/**
	 * Tests whether this node can be passed through when appending to given node
     * @param JNodeBase
	 * @return bool
	 */
	function is_transparent( JNodeBase $Parent ){
		return true && $this->length === 1;
	}

	
	
	/**
	 * @override
	 */
	function obfuscate( array &$names ){
		$Node = $this->reset();
		// it could be a new constructor
		if( $Node->is_symbol(J_NEW) ){
			$Node = $this->next();
			// recurse into member expression
			$Node->obfuscate( $names );
			// obfuscate args
			$Node = $this->next();
			$Node->obfuscate( $names );
			return;
		}
		// Else it is a member chain
		// If it is dot-delimited, we can only obfuscate the first in chain
		$Node->obfuscate( $names );
		// but bracket accessors are safe as the references inside are local
		while( $Node = $this->next() ){
			if( ! $Node->is_symbol('[') ){
				break;
			}
			$Node = $this->next();
			$Node->obfuscate( $names );
			$Node = $this->next();
		}
	}
	
	
	/**
	 * @override
	 */
	function format_lines( &$line, array &$lines ){
		$Node = $this->reset();
		// it could be a new constructor
		if( $Node->is_symbol(J_NEW) ){
			return parent::format_lines( $line, $lines );
		}
		// Else it is a member chain, no whitespace ?
		do {
			$Node->format_lines( $line, $lines );
			$line = trim( $line );
		}
		while( 
			$Node = $this->next()
		);
		$line .= ' ';
	}
	
	
	
	

}
