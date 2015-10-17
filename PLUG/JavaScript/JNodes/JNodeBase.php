<?php

namespace PLUG\JavaScript\JNodes;

/**
 * File containing class JNodeBase
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JNodeBase.php,v 1.1 2009/03/22 12:51:08 twhitlock Exp $
 */

use PLUG\parsing\LR\LRParseNode;
use PLUG\parsing\ParseNode;

/**
 * Default JavaScript node class
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JNodeBase extends LRParseNode {

	/**
	 * @override
	 */
	function push( ParseNode $Node, $recursion = true ){
		//echo "<br />\n(",$Node->length,") ",j_token_name($Node->t)," pushed onto (",$this->length,") ", j_token_name($this->t);
		if( $Node->is_transparent( $this ) ){
			//while( $Child = $Node->remove_at(0) ){
			//	//echo " COLLAPSE to ", j_token_name( $Child->t );
			//	parent::push( $Child, $recursion );
			//	unset( $Child );
			//}
			parent::push_thru( $Node );
			$Node->destroy();
			return $this->length;
		}
		// else append this single node
		return parent::push( $Node, $recursion );
	}
	
	
	
	/**
	 * Tests whether this node can be passed through when appending to given node
     * @param JNodeBase
	 * @return bool
  	 */
	function is_transparent( JNodeBase $Parent ){
		return false;
	}
	
	
	
	
	/**
	 * Overloaded method ensures that minimal whitespace is appended where appropriate to maintain syntax
	 * @return string
	 */
	function __toString(){
		if( $this->is_terminal() ){
			return parent::__toString();
		}
		$src = '';
		foreach( $this->children as $i => $Child ){
			if( $Child->is_terminal() ){
				$s = (string) $Child->value;
				switch( $Child->t ){
				// these terminals will may or may not be followed by an identifier
				// but always by the next terminal in this node.
				case J_FUNCTION:
				case J_CONTINUE:
				case J_BREAK;
					$identFollows = isset($this->children[$i+1]) && $this->children[$i+1]->is_symbol(J_IDENTIFIER);
					$identFollows and $s .= ' ';
					break;
				// these terminals will always be followed by an idenfifer
				case J_VAR:
				// these terminals are followed by a non terminal;
				// adding a space to be on the safe side.
				case J_DO:
				case J_ELSE:
				case J_RETURN:
				case J_CASE:
				case J_THROW:
				case J_NEW:
				case J_DELETE:
				case J_VOID:
				case J_TYPEOF:
					$s .= ' ';
					break;
				// these terminals require a space on either side
				case J_IN:
				case J_INSTANCEOF:
					$s = ' '.$s.' ';
					break;
				}
			}
			else {
				// recursion into non-terminal
				$s = $Child->__toString();
			}
			$src .= $s;
		}
		return $src;
	}	
	
	
	


	/**
	 * Default obfuscation routine for non-terminal nodes
	 */
	function obfuscate( array &$names ){
		// Obfuscate current scope, ignoring functions as their scopes are closed
		// and are handled by JElementsNode::obfuscate
		foreach( $this->children as $Node ){
			if( $Node->is_terminal() ){
				// identifiers are the only terminal symbol we want to call obfuscate on
				if( $Node->is_symbol(J_IDENTIFIER) ){
					$Node->obfuscate( $names );
				}
			}
			else if( ! $Node->is_symbol(J_FUNC_DECL) && ! $Node->is_symbol(J_FUNC_EXPR) ){
				$Node->obfuscate( $names );
			}
		}
	}	
	
	
	
	/**
	 * Default formatting function
	 * @param string current line
	 * @param array all lines
	 * 
	 */
	function format_lines( &$line, array &$lines ){
		if( $this->is_terminal() ){
			switch( $this->t ){
			// semicolon always terminates line
			case ';':
				$line = rtrim( $line, ' ');
				self::format_newline( $this->t, $line, $lines );	
				break;
			// comma may hug previous element; adjust superfluous space.
			case ',':
				$line = rtrim( $line, ' ');
				$line .= ', ';
				break;
			default:
				// by default add trailing space
				$line .= $this->__toString().' ';
			}
		}
		else {
			// default non-terminal method
			foreach( $this->children as $Node ){
				$line .= $Node->format_lines( $line, $lines );
			}
		}
	}
	
	
	
	/**
	 * @internal
	 */
	protected static function format_newline( $str, &$line, array &$lines ){
		$line .= $str;
		$lines[] = $line;
		$line = '';
	}
	
	
	


}
