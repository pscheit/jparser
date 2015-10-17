<?php

namespace PLUG\parsing\LR;

/**
 * File containing class LRParseNode
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage LR
 * @version $Id: LRParseNode.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */

use PLUG\parsing\ParseNode; 
 
/**
 * ParseNode class extended for LR specific functionality.
 * @category PLUG
 * @package parsing
 * @subpackage LR
 */
class LRParseNode extends ParseNode {


	/**
	 * State number
	 * @var int
	 */
	protected $s;	
	
		
	/**
	 * @param int optional state number to set
	 * @return int
	 */
	function state( $s = null ){
		$i = $this->s;
		if( ! is_null($s) ){
			$this->s = $s;
		}
		//echo '<br />state ', j_token_name( $this->t ), '#', $this->s;
		return $i;
	}
	
	
	
	/**
	 * We only need the top node on the stack to maintain state
     * and because are no roll-backs in the LRParser you can call this as you see fit to save memory
	 */
	function free_memory(){
		// depth first recursion
		foreach( $this->children as $Child ){
			$Child->free_memory();
		}
		// unset redundant variables to save memory
		//echo "<br />\nCleaning ", j_token_name( $this->t ), '#', $this->s;
		unset( $this->s );
	}
	
	
	

}
