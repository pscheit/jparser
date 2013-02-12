<?php
/**
 * File containing class PLUG.parsing.LR.LRItem
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage LR
 * @version $Id: LRItem.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */


/**
 * Item for use while generating an LR Automaton.
 * Only supports a single lookahead symbol.
 * @category PLUG
 * @package parsing
 * @subpackage LR
 */
class LRItem {

	/**
	 * pointer within rule RHS
	 * @var int
	 */
	private $i;

	/**
	 * rule id
	 * @var int
	 */
	private $r;

	/**
	 * Single lookahead terminal symbol
	 * @var mixed
	 */
	private $la;
	
	/**
	 * Rule data
	 * @var array
	 */	
	private $rule;

	
	private static $uindex = array();
	
	
	/**
	 * Constructor
	 * @param int rule id
	 * @param array rule
	 * @param int current position in rule
	 */
	private function __construct( $r, array $rule, $i ){
		$this->r = $r;
		$this->rule = $rule;
		$this->i = $i;
		// automatically move over empty symbols
		while( P_EPSILON === $this->current_symbol() ){
			$this->i++;
		}
	}	
	
	

	/**
	 * Factory method
	 */
	static function make( $r, array $rule, $i ){
		return new LRItem( $r, $rule, $i );
	}
	
		
	/**
	 * Debugging function
	 * @param Lex
	 * @param Grammar
	 * @return void
	 */
	function dump( Lex $Lex, Grammar $Grammar ){
		list( $lhs, $rhs ) = $this->rule;
		echo $Lex->name($lhs), "->";
		foreach( $rhs as $i => $s ){
			if( $i === $this->i ){
				echo '•';
			}
			else if( $i !== 0 ){
				echo ',';
			} 
			$t = $Lex->name( $s );
			echo $t === $s ? var_export($s,1) : $t;
		}
		if( is_null( $this->current_symbol() ) ){
			// accepting state
			echo '•';
		}
		if( isset($this->la) ){
			echo ' [',$Lex->name($this->la),']';
		}
	}
	
	
	
	/**
	 * @internal
	 * @return string
	 */
	function __toString(){
		return $this->r .'@'. $this->i .( isset($this->la) ? '|'.$this->la : '' );
	}		
	
	
	
	/**
	 * @internal
	 * @param Lex
	 * @param Grammar
	 * @return void
	 */
	function _print( Lex $Lex, Grammar $Grammar ){
		$this->dump( $Lex, $Grammar );
	}		

	
	
	/**
	 * Get rule if item is in an accepting state
	 * @return array, rule or null
	 */	
	function accepting_rule(){
		if( is_null( $this->current_symbol() ) ){
			return $this->rule;
		}
		else {
			return null;
		}
	}

	
	
	/**
	 * get rule if item is accepting
	 * @return int rule id, or null
	 */	
	function accepting_rule_id(){
		if( is_null( $this->current_symbol() ) ){
			return $this->r;
		}
		else {
			return null;
		}
	}
	
	
	
	/**
	 * @internal
	 * @param int
	 * @return int
	 */	
	private function get_symbol( $i ){
		if( ! isset($this->rule[1][$i]) ){
			return null;
		}
		return $this->rule[1][$i];
	}
	
	

	/**
	 * Get all symbols from given point to the end of the rule
	 * @return array
	 */
	function get_symbols( $i ){
		if( ! isset($this->rule[1][$i]) ){
			return array();
		}
		return array_slice( $this->rule[1], $i );
	}
	

	
	/**
	 * Get/set look ahead set
	 * @param mixed
	 * @return mixed
	 */	
	function lookahead( $la = null ){
		$a = $this->la;
		if( ! is_null($la) ){
			$this->la = $la;
		}
		return $a;
	}	

	
	
	/**
	 * Get symbol at current point in rule
	 * @return int
	 */
	function current_symbol(){
		return $this->get_symbol( $this->i );
	}	
	
	
	
	/**
	 * Get symbols from current point to the end of rule
	 * @return int
	 */
	function current_symbols(){
		return $this->get_symbols( $this->i );
	}	
	
	
	
	/**
	 * Get symbol at next point in rule
	 * @return int
	 */
	function next_symbol(){
		return $this->get_symbol( $this->i + 1 );
	}
	
	
	
	/**
	 * Get symbols from the next point to the end of rule
	 * @return array
	 */
	function next_symbols(){
		return $this->get_symbols( $this->i + 1 );
	}
	
	
	
	/**
	 * Advance this item to new item at next point in rule
	 * @return LRItem
	 */
	function make_next(){
		$Item = self::make( $this->r, $this->rule, $this->i+1 );
		$Item->la = $this->la;
		return $Item;
	}

	
	
		
}

