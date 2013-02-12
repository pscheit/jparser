<?php
/**
 * File containing class PLUG.parsing.LR.LRItem
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage LR
 * @version $Id: LRState.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */


/**
 * State object for use while generating an LR automaton.
 * An LR automaton state contains a single {@link LRItem}.
 * It contains none or more e-transitions to {@link LRStation}s.
 * It contains none or more transtions to further states (one transition max for each symbol).
 * @category PLUG
 * @package parsing
 * @subpackage LR
 */
class LRState {

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
	 * global unique index
	 * @var array
	 */
	public static $uindex = array();

	/**
	 * The item in this state
	 * @var array
	 */
	public $Item;

	/**
	 * Epsilon-transtions to LRStations
	 * @var array
	 */	
	public $etransitions;
	
	/**
	 * Transtions to following LRStates, one per symbol
	 * @var array
	 */	
	public $transitions;	
	
	/**
	 * 
	 */
	private static $threadInc = 0;
	
	/**
	 * 
	 */
	private $threadId;
	

	/**
	 * Constructor
	 * @param array intial array of LRItems
	 */
	protected function __construct( LRItem $Item ){
		$this->id = ++self::$incid;
		$this->Item = $Item;
	}
	
	
	
	/**
	 * Start recursive collection of transtions to stations and states
	 * @param Grammar 
	 * @param int number of symbols to look ahead, currently only 0 or 1
	 * @return Void
	 */
	function collect_non_deterministic( Grammar $Grammar, $k ){
		
		// avoid recursion by testing if already collected		
		if ( isset($this->transitions) ){
			return;
		}
		$this->transitions = array();	
		$this->etransitions = array();	
		$s = $this->Item->current_symbol();
		$la = $this->Item->next_symbol();
		
		#1. accept if end of rule
		if( is_null($s) ){
			return;
		}
		#2. e-transition to station if symbol is non-terminal
		if( $Grammar->non_terminal($s) ){
			// LR(0) has no lookahead
			if( $k === 0 ){
				$las = array( null );
			}
			// else there are three situations for moving to a new station:
			// a. This is the last symbol so we carry through existing lookahead, e.g. "..•X [a]"
			else if( is_null($la) ){
				// use current look ahead
				$las = array( $this->Item->lookahead() );
			}
			// b. Look ahead is terminal following current non terminal e.g. "..•Xa.."
			else if( $Grammar->is_terminal($la) ){
				// use terminal as next lookahead
				$las = array( $la );
			}
			// c. Non-terminal follows this non-terminal, e.g. "..•XYZ..."
			//    Page 207 & 211 of Parsing Guide says calculate FIRST(YZ..) 
			//    But FOLLOW(X) can result in more lookaheads as it consults other rules no in this item.
			else {
				// -- FOLLOW method -------
				$las = $Grammar->follow_set( $s );
				// -- FIRST method -------
				//$ss = $Item->next_symbols();
				//$ss[] = $Item->lookahead();
				//$las = $Grammar->first_set( $ss );
			}
			// create a station for each lookahead symbol
			foreach( $las as $la ){
				$Station = LRStation::make( $s, $la );
				$Station->collect_non_deterministic( $Grammar, $k );
				$this->etransitions[] = $Station;
			}
		}
		#3. Branch advanced item to a new state
		$nextItem = $this->Item->make_next();
		$State = LRState::make( $nextItem );
		$State->collect_non_deterministic( $Grammar, $k );
		$this->transitions[$s] = $State;
	}
	
	
	
	
	/**
	 * Collect siblings states including self.
	 * It is assumed that we only have one item at this point
	 * @param array reference to populate
	 * @return void
	 */	
	function collect_states_passthru( &$states, Grammar $Grammar, array $excluded, $threadId ){
		if( isset( $states[ $this->id ] ) ){
			// recursion
			return;
		}
		// check exclusions - These should only be passed when calling from a station, 
		// so we know there is only one item and it is at the beginning of the rule.
		if( $excluded ){
			$s = $this->Item->current_symbol();
			if( in_array($s,$excluded,true) ){
				//echo "Excluding state #$this->id because it begins with $s\n";
				return;
			}
			if( $Grammar->is_terminal($s) ){
				//echo "Clearing exclude list in state #$this->id for descent beyond $s\n";
				$excluded = array();
			}
			//else {
			//	echo "Passing down ",count($excluded)," exclusions through state #$this->id \n";
			//}
		}
		$states[ $this->id ] = $this;
		foreach( $this->etransitions as $Station ){
			$Station->collect_states_recursive( $states, $Grammar, $excluded, $threadId );
		}
	}

	
	
	/**
	 * Factory method to ensure unique object instances globally
	 * @param LRItem
	 * @return LRState
	 */
	static function make( LRItem $Item ){
		$sig = (string) $Item;
		// check global index
		if( isset( self::$uindex[$sig] ) ){
			return self::$uindex[$sig];
		}
		// else construct
		$State = new LRState( $Item );
		self::$uindex[$sig] = $State;
		return $State;
	}

	
	
	
	/**
	 * Clear registered states
	 * @return void
	 */
	static function clear_index(){
		self::$uindex = array();
		self::$incid = 0;
	}
	

	
	
	/**
	 * @internal
	 * @return void
	 */	
	function dump( Lex $Lex, Grammar $Grammar, $tab= '', $noheader = false ){
		if( ! $noheader ){
			echo "{$tab}LRState #$this->id\n";
		}
		if( isset($this->__dumped) ){
			echo "{$tab}--recursion--\n";
			return;
		}
		echo "{$tab}( ";
		$this->Item->dump( $Lex, $Grammar );
		echo " )\n";
		$this->__dumped = true;
		if( isset($this->etransitions) ){
			foreach( $this->etransitions as $Station ){
				echo "{$tab} --->\n";
				$Station->dump( $Lex, $Grammar, "$tab . " );
			}
		}
		if( isset($this->transitions) ){
			foreach( $this->transitions as $s => $State ){
				echo "{$tab}`",$Lex->name($s),"' -->\n";
				$State->dump( $Lex, $Grammar, "$tab . ", $noheader );
			}
		}
	}		
	
	
	
	/**
	 * @internal
	 * @return string
	 */	
	function __toString(){
		return "[LRState #$this->id]";
	}
	


	
	/**
	 * Release all references to stations to free up memory
	 * @return void
	 */
	static function release_stations(){
		foreach( self::$uindex as $sig => $State ){
			$State->etransitions = array();
		}
	}
		
	
	
}



