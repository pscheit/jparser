<?php
/**
 * File containing class BNFRuleNode
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage bnf
 * @version $Id: BNFRuleNode.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */

 
/**
 * 
 * @category PLUG
 * @package parsing
 * @subpackage bnf
 */
class BNFRuleNode extends LRParseNode {

	/**
	 * @override
	 */
	function evaluate(){
		$this->reset();
		// we constist of '<', BNF_TEXT, '>', ':', BNF_EXPRESSION, BNF_RULE_END
		// or empty rule just BNF_RULE_END
		switch( $this->length ){
		case 1:
			return null;
		case 6:
			$nt = $this->next()->evaluate();
			if( preg_match('/^\w/', $nt, $r ) && defined($nt) ){
				$nt = constant( $nt );
			}
			$this->next();
			$this->next();
			return array( $nt, $this->next()->evaluate() );
		default:
			trigger_error( "Unexpected length for BNF_EXPRESSION node, $this->length", E_USER_WARNING );
		}
	}
	



	/**
	 * @override
	 */	
	function __toString(){
		$this->reset();
		switch( $this->length ){
		case 1:
			return '';
		case 6:
			$nt = $this->next();
			$this->next();
			$this->next();
			$Rhs = $this->next();
			return "$nt => $Rhs";
		default:
			trigger_error( "Unexpected length for BNF_EXPRESSION node, $this->length", E_USER_WARNING );
		}
	}

	
	
	
	
	

	
}
	
 
 
?>