<?php
/**
 * File containing class LRNDA
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage LR
 * @version $Id: LRNDA.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */
 
 
import('PLUG.parsing.LR.LRStation');
import('PLUG.parsing.LR.LRStateSet');
import('PLUG.parsing.LR.LRItem');
import('PLUG.parsing.LR.LRParseTableBuilder');
 

/**
 * Non-determinsitic automaton generator for building LR parse tables.
 * Can generate LR(0) and LR(1) automata.
 * Object is a root {@link LRStation} node descending into a recursive tree of {@link LRState}s.
 * @example ../../bin/lrtable See command line LR parse table gernerator
 * @category PLUG
 * @package parsing
 * @subpackage LR
 */
class LRNDA extends LRStation {
	
	
	/**
	 * @var object Grammar
	 */
	private $Grammar;
	
	
	/**
	 * Constructor
	 * @param Grammar 
	 * @param int number of symbols to look ahead, currently only 0 or 1, defaults to 0
	 */
	function __construct( Grammar $Grammar, $k = 0 ){
		if( $k !== 0 && $k !== 1 ){
			trigger_error( "LR(".var_export($k,1).") not supported, only LR(0), or LR(1)", E_USER_ERROR );
		}
		parent::__construct( P_GOAL, null );
		LRState::clear_index();
		$this->Grammar = $Grammar;
		$this->collect_non_deterministic( $Grammar, $k );
	}
	

	
	/**
	 * Export to determinsitic parse table.
	 * @return LRParseTable
	 */	
	function export(){
		$RootSet = $this->resolve();
		$table = array();
		$RootSet->export( $table, $this->Grammar );
		return new LRParseTableBuilder( $table );
	}


	
	/**
	 * Resolve non-deterministic automaton into a deterministic state tree
	 * @return LRStateSet
	 */
	function resolve(){
		LRState::clear_index();
		LRStation::clear_index();
		// create Root Set
		// we SHOULD have a single etransition to an intial state
		return LRStateSet::init( $this->etransitions[0], $this->Grammar );
	}
	
	
	
		
}




