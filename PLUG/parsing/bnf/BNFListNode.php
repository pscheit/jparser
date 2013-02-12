<?php
/**
 * File containing class BNFListNode
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage bnf
 * @version $Id: BNFListNode.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */

 
 
/**
 * 
 * @category PLUG
 * @package parsing
 * @subpackage bnf
 */
class BNFListNode extends LRParseNode {

	/**
	 * @override
	 */
	function evaluate(){
		if( ! $this->length ){
			return array();
		}	
		// we constist of one or more terms
		// this assumes that recursion has been resolved by root node
		$Term = $this->reset();
		$list = array();
		do {
			$list[] = $Term->evaluate();
		}
		while(
			$Term = $this->next()
		);
		return $list;
	}
	




	/**
	 * @override
	 */
	function __toString(){
		if( ! $this->length ){
			return '';
		}	
		// we constist of one or more terms
		// this assumes that recursion has been resolved by root node
		$Term = $this->reset();
		$terms = array();
		do {
			$terms[] = (string) $Term;
		}
		while(
			$Term = $this->next()
		);
		return "array( ".implode(', ', $terms )." ), ";
	}
	
}
	
 
 
?>