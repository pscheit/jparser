<?php
/**
 * File containing class LRStateSet
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage LR
 * @version $Id: LRStateSet.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */
 
 

/**
 * 
 * @category PLUG
 * @package parsing
 * @subpackage LR
 */
class LRStateSet {

	/**
	 * Incremental odd id number
	 */
	protected static $incid = 1;
	
	/**
	 * odd numbered state id
	 * @var int
	 */	
	public $id;

	/**
	 * Index of all LRStateSets
	 * @var array
	 */
	private static $uindex = array();
	
	
	public $states = array();
	
	
	private $transitions = array();
	
	
	private static $Grammar;
	
	
	
	/**
	 * Constructor
	 */
	private function __construct( array $states ){
		$this->id = self::$incid;
		self::$incid += 2;
		$this->states = $states;
	}
	
	
	
	/**
	 * 
     */
	function __toString(){
		return "[LRStateSet #$this->id]";
	}
	
	
	
	/**
	 * 
	 */
	private function resolve(){
		// create transitions to new state sets
		$transitions = array();
		foreach( $this->states as $State ){
			foreach( $State->transitions as $s => $SubState ){
				$transitions[$s][ $SubState->id ] = $SubState;
			}
		}
		foreach( $transitions as $s => $states ){
			$this->transitions[$s] = self::make( $states );
		}
	}
	
	
	
	/**
	 * 
	 */
	static function make( array $states ){
		// signature defined by initial states only
		ksort( $states, SORT_NUMERIC );
		$sig = implode('|', array_keys($states) );
		if( isset(self::$uindex[$sig]) ){
			return self::$uindex[$sig];
		}
		// augment intitial states with moves to further stations
		$stations = array();
		foreach( $states as $State ){
			foreach( $State->etransitions as $Station ){
				$stations[$Station->id] = $Station;
			}
		}
		foreach( $stations as $Station ){
			foreach( $Station->collect_states(self::$Grammar) as $State ){
				$states[ $State->id ] = $State;
			}
		}
		$Set = self::$uindex[$sig] = new LRStateSet( $states );
		$Set->resolve();
		return $Set;
	}
	
	
	
	/**
	 * 
	 */
	static function init( LRState $InitialState, Grammar $Grammar ){
		self::$Grammar = $Grammar;
		unset( $Grammar );
		// Start recurive process by creating root set
		return self::make( array( $InitialState->id => $InitialState ) );
	}
	
	

	/**
	 * @internal
	 * Export to table.
	 * @param array reference to table array
	 * @param Grammar
	 * @return void
	 */	
	function export( array &$table ){
		// recursion protection
		if( isset($table[$this->id]) ){
			return;
		}
		// create row for state
		$table[$this->id] = array();
		if( empty($this->states) ){
			throw new Exception("State $this->id is empty, automaton is incomplete or in error");
		}
		
		// collect shift and reduce moves separately
		$reductions = array();
		$shifts = array();
		foreach( $this->states as $State ){
			$s = $State->Item->current_symbol();
			$r = $State->Item->accepting_rule_id();
			$accept = ! is_null( $r );
			$la = $State->Item->lookahead();
			
			if( $accept ){
				if( $la === null ){
					// FINAL accepting state there should only be one entry in this column and only one entry in this row
					// This will never actually be looked up because parser will stop as soon as EOF is shifted
					// but it needs to go into the table for process to complete - not sure why, go ahead - try removing it!
					$la = P_GOAL;
				}
				//echo "state #$this->id with lookahead \"$la\" accepts with rule $r\n";
				if( isset($reductions[$la]) && $reductions[$la] !== $r ){
					// Conflict: Reduction already exists for a different rule
					$message = "State $this->id has reduce/reduce conflict (r{$reductions[$la]}/r$r) with lookahead ".var_export($la,1);
					$resolve = self::$Grammar->resolve_reduce_conflict( $reductions[$la], $r, $la );
					switch( $resolve ){
					// use new rule if resolver returned 1
					case 1:
						//trigger_error( "$message, resolver says use new r$r", E_USER_NOTICE );
						break;
					// or use existing rule if resolver returned -1
					case -1:
						//trigger_error( "$message, resolver says keep r{$reductions[$la]}", E_USER_NOTICE );
						continue 2;
					// else keep existing and warn
					default:
						trigger_error( $message, E_USER_WARNING );
						continue 2;
					}
				}
				else if( isset($shifts[$la]) ){
					// Conflict: Shift already exists where we want to reduce
					$message = "State $this->id has shift/reduce conflict (s{$shifts[$la]->id}/r$r) on lookahead symbol ".var_export($la,1);
					$resolve = self::$Grammar->resolve_shift_conflict( $shifts[$la], $r, $la );
					switch( $resolve ){
					// keep shift, if resolver returned -1
					case -1:
						//trigger_error( "$message, resolver says keep the shift", E_USER_NOTICE );
						continue 2;
					// reduce if resolver returned 1
					case 1:
						//trigger_error( "$message, resolver says reduce", E_USER_NOTICE );
						unset( $shifts[$la] );
						break;
					// else rule of thumb is to keep shift
					default:
						trigger_error( "$message, keeping the shift by default", E_USER_WARNING );
						continue 2;
					}
				}
				// Use this reduction
				$reductions[$la] = $r;
			}
			else if( isset($shifts[$s]) ){
				// ?? echo "state #$this->id with symbol \"$s\" already shifting to ", $this->transitions[$s]->id,"\n";
			}
			
			//child state must exist for this symbol so we can shift to it
			else if( isset($this->transitions[$s]) ){
					
				if( isset($reductions[$s]) ){
					// Conflict: Reduction already exists
					$r = $reductions[$s];
					$toState = $this->transitions[$s];
					$message = "State $this->id has shift/reduce conflict (s{$toState->id}/r$r) on lookahead symbol ".var_export($la,1);
					$resolve = self::$Grammar->resolve_shift_conflict( $toState, $r, $la );
					switch( $resolve ){
					// shift, if resolver returned -1
					case -1:
						//trigger_error( "$message, resolver says shift", E_USER_NOTICE );
						unset($reductions[$s]);
						break;
					// keep reduction if resolver returned 1
					case 1:
						//trigger_error( "$message, resolver says keep the reduction", E_USER_NOTICE );
						continue 2;
					// else rule of thumb is to shift
					default:
						trigger_error( "$message, shifting by default", E_USER_WARNING );
						unset($reductions[$s]);
					}
				}
				// remember this shift move to state
				$shifts[$s] = $this->transitions[$s];
			}
			else {
				// redundant shift, no need to enter anything into the table
				trigger_error("state #$this->id with symbol $s has a redundant shift", E_USER_NOTICE );
			}
		}
		// errors all checked, create table entries and descend into following states
		foreach( $reductions as $s => $r ){
			// $r should be an even rule number
			$table[$this->id][$s] = $r;
		}
		foreach( $shifts as $s => $State ){
			// $State->id should be an odd state number (row index)
			$table[$this->id][$s] = $State->id;
			// descend
			$State->export( $table );
		}
	}
	
	
	
	
	/**
	 * @internal
	 * @return void
	 */	
	function dump( Lex $Lex, Grammar $Grammar, $tab = '', $noheader = false ){
		if( ! $noheader ){
			echo "{$tab}LRStateSet #$this->id\n";
		}
		if( isset($this->__dumped) ){
			echo "{$tab}--recursion--\n";
			return;
		}
		$this->__dumped = true;
		echo "{$tab}(";
		foreach( $this->states as $State ){
			echo "\n{$tab} ";
			$State->Item->dump( $Lex, $Grammar );
		}
		echo "\n{$tab})\n";
		foreach( $this->transitions as $s => $State ){
			echo "{$tab}`",$Lex->name($s),"' -->\n";
			$State->dump( $Lex, $Grammar, "$tab . ", $noheader );
		}
	}

}

