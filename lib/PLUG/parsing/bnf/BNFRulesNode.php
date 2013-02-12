<?php
/**
 * File containing class BNFRulesNode
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage bnf
 * @version $Id: BNFRulesNode.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */

 
/**
 * Root node for grammar rules in BNF format
 * @category PLUG
 * @package parsing
 * @subpackage bnf
 */
class BNFRulesNode extends LRParseNode {
	
	/**
	 * @internal
	 */	
	private $startsymbol;	


	/**
	 * @override
	 */
	function evaluate(){
		if( ! $this->length ){
			return null;
		}
		// we now consist of one or more rule
		$grammar = array();
		$Rule = $this->reset();
		do {
			$dat = $Rule->evaluate();
			if( ! is_null($dat) ){
				list( $s, $rules ) = $dat;
				$grammar[$s] = $rules;
			}
		}
		while( 
			$Rule = $this->next()
		);
		return $grammar;
	}
	
	
	
	
	/**
	 * pseudo var_export function
	 * cannot just use var_export, because we want constants intact and not evaluated
	 * @override
	 */	
	function __toString(){
		if( ! $this->length ){
			return '';
		}
		// we now consist of one or more rule
		$s = "array( ";
		$Rule = $this->reset();
		do {
			$s .= (string) $Rule;
		}
		while( 
			$Rule = $this->next()
		);
		$s .= " )";
		return $s;
	}	
	
	
	
	
	/**
	 * Recursively collect terminal and nonterminal strings from Term nodes.
	 * @todo could take this opportunity to test for redundant or unreachable non-terminals
	 * @return array [ [terminala,terminalb,..], [nonterminalX,nonterminalY,..] ]
	 */
	function collect_symbols(){
		if( ! $this->length ){
			return null;
		}
		$Rule = $this->reset();
		do {
			if( $Rule->length !== 6 ){
				// empty rule most likely
				continue;
			}
			// collect rule name - guaranteed to be nontermainal
			$Name = $Rule->get_child( 1 );
			$n = $Name->evaluate();
			$nts[ $n ] = $n;
			
			// assume first nt is start symbol
			if( is_null( $this->startsymbol ) ){
				$this->startsymbol = $n;
			}
			
			$Expr = $Rule->get_child( 4 );
			// lists in Expression
			$List = $Expr->reset();
			do {
				// terms in list - may be terminal or nonterminal
				$Term = $List->reset();
				do {
					$s = $Term->evaluate();
					switch( $Term->length ){
					case 1:
						// "terminal" or terminal
						if( $s === P_EPSILON || $s === P_EOF ){
							// mandatory symbols already known
							break;
						}
						$ts[ $s ] = (string) $Term;
						break;
					case 3:
						// angle-bracketed <nonterminal>
						$nts[ $s ] = (string) $Term;
						break;
					}
				}
				while(
					$Term = $List->next()
				);
			}
			while( 
				$Expr->next() && $List = $Expr->next()
			);
			
		}
		while(
			$Rule = $this->next()
		);

		return array( $ts, $nts );
	}	


	
	
	/**
	 * Create a Lex instance fro symbols
	 * @param int Lowest value for new token constant definitions, defaults to 0 
	 */
	function make_lex( $i = 0 ){
		$Lex = new LexBuilder( $i );
		foreach( $this->collect_symbols() as $symbols ){
			foreach( $symbols as $t => $s ){
				if( preg_match('/^\W/', $s, $r ) ){
					$Lex->define_literal( (string) $t );
				}
				else if( $Lex->defined($s) ){
				}
				else if( defined($s) ){
					$Lex->redefine( $s );
				}
				else {
					$Lex->define( $t );
				}
			}
		}
		return $Lex;
	}
	
	
}
