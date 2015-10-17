<?php

namespace PLUG\parsing;

/**
 * File containing class Grammar
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @version $Id: Grammar.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */

use Exception;
use PLUG\parsing\LR\LRStateSet;

/**
 * Grammar base class
 * @category PLUG
 * @package parsing
 */
abstract class Grammar {
		
	/**
	 * Start symbol, will be guessed at first, but can be set publicly with start_symbol
	 * @var int
	 */	
	protected $goal;

	/**
	 * Even-numbered index for next rule
	 * @var int
	 */	
	protected $i = 0;
	
	/**
	 * global list of all rules, indexed by even numbers
	 * @var array
	 */
	protected $rules = array();
	
	/** 
	 * Index of disallowed terminal symbols as first symbol of non-terminals
	 * @var array
	 */
	protected $excludela = array();
	
	/**
	 * registry of terminal symbols
	 * @var array
	 */	
	protected $ts = array (
		P_EPSILON => P_EPSILON,
		P_EOF => P_EOF
	);	
	
	/**
	 * registry of non-terminal symbols
	 * @var array
	 */	
	protected $nts = array();	
	
	/**
	 * cache of first sets
	 * @var array
	 */		
	protected $firsts;	
	
	/**
	 * cache of follow sets
	 * @var array
	 */		
	protected $follows;	
	
	/**
	 * Index of list entries indexed by rule signature
	 * @var array
	 */	
	protected $uindex;
	
	/**
	 * Index of list entries grouped by non-terminal LHS symbol
	 */	
	protected $ntindex; 

	
	/**
	 * Check special rules where a non-terminal is not allowed to start with certain terminals
	 */
	function excluded_terminals( $nt ){
		if( isset($this->excludela[$nt]) ){
			return $this->excludela[$nt];
		}
		else {
			return array();
		}
	}
	
	
	/**
	 * Create exclusion case
	 * @param int|string non-terminal
	 * @param int|string terminal symbol disallowed as first symbol of non-terminal
	 * @return void
	 */
	function exclude_terminal( $nt, $t ){
		$this->excludela[$nt][] = $t;
	}
	
	
	
	/**
	 * Default conflict resover for a reduce/reduce conflict in an LR parse table generator
	 * @param int id of the rule to which it is currenly reducing
	 * @param int id of the rule to which it subsequenlt wants to reduce
	 * @param mixed look ahead symbol
     * @return int -1 to use existing reduction, 1 to use new reduction, 0 to concede failure
     */
	function resolve_reduce_conflict( $r1, $r2, $la ){
		// Rule of thumb is to use the longest rule
		$rule1 = $this->get_rule( $r1 );
		$rule2 = $this->get_rule( $r2 );
		$len1 = count($rule1);
		$len2 = count($rule2);
		if( $len2 > $len1 ){
			return 1;
		}
		else if( $len1 > $len2 ){
			return -1;
		}
		else {
			return 0;
		}
	}	
	
	

	/**
	 * Default conflict resover for a shift/reduce conflict in an LR parse table generator
	 * @param LRState the state that it wants to shift to
	 * @param int id of the rule it wants to reduce to
     * @return int -1 to shift, 1 to reduce, 0 to concede failure
     */
	function resolve_shift_conflict( LRStateSet $toState, $r, $la ){
		return 0;
	}
	
	
	
	/**
	 * Get rule by number
	 * @return array
	 */	
	function get_rule( $i ){
		return $this->rules[ $i ];
	}
	
	
	
	/**
	 * Get rules by non-terminal left hand side
	 * @param int|string non terminal symbol
	 * @return array none or more rule arrays
	 */	
	function get_rules( $nt ){
		$rules = array();
		if( isset($this->ntindex[$nt]) ){
			foreach( $this->ntindex[$nt] as $i ){
				$rules[$i] = $this->rules[$i]; 
			}
		} 
		return $rules;
	}



	/**
	 * @return bool
	 */	
	function is_terminal( $s ){
		is_array( $s ) and $s = $s[0];
		return isset( $this->ts[$s] );
	}
	
	
	
	/**
	 * @return bool
	 */	
	function non_terminal( $s ){
		is_array( $s ) and $s = $s[0];
		return isset( $this->nts[$s] );
	}
	
	
	
	
	/**
	 * @return array
	 */	
	function export(){
		return $this->rules;
	}	
	
	
	
	/**
	 * @ignore
	 */	
	function dump( Lex $Lex ){
		$t = max( 2, strlen( (string) $this->i ) );
		foreach( $this->rules as $i => $rule ){
			$lhs = $Lex->name( $rule[0] );
			$rhs = $Lex->implode( ' ', $rule[1] );
			$i = str_pad( $i, $t, ' ', STR_PAD_LEFT );
			echo "$i: $lhs -> $rhs \n";
		}
		if( $this->excludela ){
			echo "Special rules:\n";
			foreach( $this->excludela as $nt => $a ){
				echo ' ',$Lex->name($nt), ' ~{ ',$Lex->implode(' ',$a)," }\n";
			}
		}
	}
	
	
	
	/**
	 * Get FIRST set for a given sequence of non-terminals
	 * @param array list of scalar symbols
	 * @return array set of terminal symbols
	 */
	function first_set( array $sequence ){
		// build set with utility func
		return $this->make_set( $this->firsts, $sequence, 'FIRST' );
	}		
	
	
	
	
	/**
	 * Get FOLLOW set for a single given symbol
	 * @param int scalar symbol
	 * @return array set of terminal symbols
	 */
	function follow_set( $s ){
		if( ! isset($this->follows[$s]) ){
			$type = $this->is_terminal($s) ? 'terminal' : 'non-terminal';
			trigger_error("No follow set for $type $s", E_USER_WARNING );
			return array();
		}
		return $this->follows[$s];
	}	
		

		
	/**
	 * @internal
	 */
	protected function make_set( array $sets, array $sequence, $type ){
		// build set to return
		$set = array();
		$s = reset( $sequence );
		do {
			$derives_e = false;
			if( $s === P_EPSILON ){
				$derives_e = true;
			}
			else if( $this->is_terminal($s) ){
				$set[$s] = $s;
			}
			else if( !isset($sets[$s]) ){
				throw new Exception( "No $type($s)" );
				break;
			}
			else {
				foreach( $sets[$s] as $t ){
					if( $t === P_EPSILON ){
						$derives_e = true ;
					}
					else {
						$set[$t] = $t;
					}
				}
			}
		}
		while( $derives_e && ( $s = next($sequence) ) && $s !== P_EOF );
		return $set;
	}	

	
	

}






