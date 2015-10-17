<?php

namespace PLUG\parsing\bnf;

/**
 * File containing class BNFExpressionNode
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage bnf
 * @version $Id: BNFExpressionNode.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */

use PLUG\parsing\LR\LRParseNode;

/**
 * 
 * @category PLUG
 * @package parsing
 * @subpackage bnf
 */
class BNFExpressionNode extends LRParseNode {

	/**
	 * @override
	 */
	function evaluate(){
		if( ! $this->length ){
			return array();
		}
		// Expression is one or more lists separated by `|'s
		// this assumes that recursion has been resolved by root node
		$lists = array();
		$List = $this->reset();
		do {
			$lists[] = $List->evaluate();
		}
		while (
			$this->next() && $List = $this->next()
		);
		
		return $lists;
	}
	
	
	
	
	/**
	 * @override
	 */
	function __toString(){
		if( ! $this->length ){
			return '';
		}
		// Expression is one or more lists separated by `|'s
		// this assumes that recursion has been resolved by root node
		$s = '';
		$List = $this->reset();
		do {
			$s .= (string) $List;
		}
		while (
			$this->next() && $List = $this->next()
		);
		return "array( $s ), ";		
	}


	
	
	
}
	
 
 
?>