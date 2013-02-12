<?php
/**
 * File containing class LRItem
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage LR
 * @version $Id: LRStation.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */


/**
 * Station object for use while generating an LR Automaton
 * It contains one or more e-transitions to LRStates
 * @category PLUG
 * @package parsing
 * @subpackage LR
 */
class LRStation {

	/**
	 * Incremental number
	 */
	private static $incid = 0;
	
	/**
	 * station id
	 * @var int
	 */	
	public $id;

	/**
	 * global unique index
	 * @var array
	 */
	public static $uindex = array();
	
	/**
	 * Non terminal symbol
	 * @var int
	 */
	public $nt;
	
	/**
	 * Single lookahead terminal
	 * @var mixed
	 */
	public $la;
		
	/**
	 * transtions to LRStates
	 * @var array
	 */	
	public $etransitions;	
	
	/**
	 * 
	 */
	static $threadInc = 0;
	
	/**
	 * 
	 */
	private $threadId;
	
	/**
	 * Private constructor
	 * @param int non-terminal symbol
	 * @param int|string optional lookahead symbol
	 */	
	protected function __construct( $nt, $la ){
		$this->nt = $nt;
		$this->la = $la;
		$this->id = ++self::$incid;
		self::$uindex[$nt][$la] = $this;
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
	 * Start recursive collection of e-transtions to states
	 * @param Grammar 
	 * @param int number of symbols to look ahead, currently only 0 or 1
	 * @return void
	 */
	function collect_non_deterministic( Grammar $Grammar, $k ){
		
		// avoid recursion by testing if already collected
		if ( isset($this->etransitions) ){
			return;
		}
		$this->etransitions = array();
		
		// get all rules with our non-terminal as left had side
		foreach( $Grammar->get_rules($this->nt) as $r => $rule ){
			// create single Item for new state with pointer at start of rule
			$Item = LRItem::make( $r, $rule, 0 );
			if( isset($this->la) && $k === 1 ){ 
				$Item->lookahead( $this->la );
			}
			// create e-transition to state with single item
			$State = LRState::make( $Item );
			$State->collect_non_deterministic( $Grammar, $k );
			$this->etransitions[] = $State;
		}
	}

	
	
	/**
	 * Collect all child states passing through any e-transitions to further stations
	 * @return array
	 */
	function collect_states( Grammar $Grammar ){
		$states = array();
		// start recursion
		$this->collect_states_recursive( $states, $Grammar, array(), ++self::$threadInc );
		return $states;
	}
	
	
	
	
	/**
	 * Recursive counterpart to collect_states()
	 * @param array reference to populate
	 * @return void
	 */
	function collect_states_recursive( array &$states, Grammar $Grammar, array $excluded, $threadId ){
		// recursion check
		if( $this->threadId === $threadId ){
			//echo "Recursion in $this \n";
			return;
		}
		$this->threadId = $threadId;
		// add denied first terminals into array
		$excluded = array_merge( $excluded, $Grammar->excluded_terminals($this->nt) );
		// Follow e-transitions recursively to gather all possible states
		foreach( $this->etransitions as $s => $State ){
			$State->collect_states_passthru( $states, $Grammar, $excluded, $threadId );
		}
	}
	

	
	
	/**
	 * @internal
	 * @return string
	 */	
	function __toString(){
		return '['.$this->nt.( isset($this->la) ? ' '.$this->la : '').']';
	}		
	
	
	
	/**
	 * @internal
	 * @return void
	 */	
	function _print( Lex $Lex ){
		echo '[', $Lex->name($this->nt), ( isset($this->la) ? ' '.$this->la : ''), ']';
	}	
	
	

	/**
	 * @internal
	 * @return void
	 */	
	function dump( Lex $Lex, Grammar $Grammar, $tab = '' ){
		echo "{$tab}LRStation #$this->id\n";
		echo "{$tab}[", $Lex->name($this->nt);
		if( isset($this->la) ){
			echo ' '.$Lex->name($this->la);
		}
		echo "]\n";
		if( isset($this->__dumped) ){
			echo "{$tab}--recursion--\n";
			return;
		}
		$this->__dumped = true;
		foreach( $this->etransitions as $State ){
			echo "{$tab}--->\n";
			$State->dump( $Lex, $Grammar,  "$tab . " );
		}
	}	



	/**
	 * Factory method to ensure unique object instances globally
	 * And stored in global index in an accessible way
	 * @param int non-terminal symbol
	 * @param int|string optional lookahead symbol
	 * @return LRStation
	 */
	static function make( $nt, $la = null ){
		if( isset(self::$uindex[$nt][$la]) ){
			return self::$uindex[$nt][$la];
		}
		return new LRStation( $nt, $la );
	}
	
	
	
	/**
	 * Getter method that consults global index
	 * @param int non-terminal symbol
	 * @param int|string optional lookahead symbol
	 * @return LRStation
	 */
	static function get( $nt, $la = null ){
		if( isset(self::$uindex[$nt][$la]) ){
			return self::$uindex[$nt][$la];
		}
		return null;
	}
	
	

	/**
	 * Clear registered states
	 * @return void
	 */
	static function clear_index(){
		self::$uindex = array();
		self::$incid = 0;
	}
	
	


}

