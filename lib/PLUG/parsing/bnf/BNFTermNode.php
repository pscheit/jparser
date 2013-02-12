<?php
/**
 * File containing class BNFTermNode
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage bnf
 * @version $Id: BNFTermNode.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */

 
 
/**
 * 
 * @category PLUG
 * @package parsing
 * @subpackage bnf
 */
class BNFTermNode extends LRParseNode {

	/**
	 * @override
	 */
	function evaluate(){
		switch( $this->length ){
		
		case 1:
			// "terminal" | terminal
			$s = $this->reset()->evaluate();
			if( $s{0} === '"' || $s{0} === "'" ){
				$s = stripslashes( substr( $s, 1, strlen($s)-2 ) );
				$literal = true;
			}
			break;
		
		case 3:
			// <non-terminal>
			$this->reset();
			$s = $this->next()->evaluate();
			break;
		
		default:
			trigger_error( "Unexpected length for BNF_TERM node, $this->length", E_USER_WARNING );
			return null;
		}
		//if( $s === 'EPSILON' ){
		//	return EPSILON;
		//}
		// We shall evaluate any constants that exist, like EPSILON
		// be warned !! use unique identifiers in your BNF files and avoid NULL
		if( ! isset($literal) && preg_match('/^\w/', $s, $r ) && defined($s) ){
			return constant($s);
		}	
		else {
			return $s;
		}
	}
	
	
	/**
	 * @override
	 */
	function __toString(){
		switch( $this->length ){
		
		case 1:
			// "terminal", or terminal
			return $this->reset()->evaluate();
		
		case 3:
			// <non-terminal>
			$this->reset();
			return $this->next()->evaluate();
		
		default:
			trigger_error( "Unexpected length for BNF_TERM node, $this->length", E_USER_WARNING );
			return '"error"';
		}
	}



	


	
	
}
	
 
 
?>