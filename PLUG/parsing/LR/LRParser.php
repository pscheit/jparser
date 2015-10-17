<?php

namespace PLUG\parsing\LR;

/**
 * File containing class LRParser
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage LR
 * @version $Id: LRParser.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */

 
use PLUG\parsing\Parser; 
use PLUG\parsing\LR\LRParseTable;
use PLUG\parsing\LR\LRParseNode;
 
 
/**
 * An LR parser.
 * @category PLUG
 * @package parsing
 * @subpackage LR
 */
abstract class LRParser extends Parser {
	
	/**
	 * Parse table
	 * @var object LRParseTable
	 */
	protected $Table;
	
	/**
	 * Stack of partial parse trees.
	 * Each entry in the tree is a LRParseNode instance with has a state number. 
	 * Eventually there will be one entry which is the goal symbol with the starting state of 1.
	 * @var array
	 */	
	protected $stack = array();
	 
	/**
	 * Parse tree
	 * @var object ParseNode
	 */
	protected $Tree;

	/**
	 * overloaded default parse node
	 * @var string
	 */
	protected $default_node_class = LRParseNode::class;
	

	/**
 	 * main entry function
	 * @param array input tokens
	 * @return LRParseNode parse tree
	 */
	function parse( array $input ){
		
		// Parser class initialized input in standard fashion
		parent::init( $input );
		
		// we always start at state 1
		$state = 1;
		
		// repeat until success or failure	
		// if table is correct, this cannot get into infinite loop
		while( true ) {
			
			//echo "\nState #$state, Symbol \"",$this->Lex->name($this->t),"\" \n";
			//echo "Permitted: [", $this->Lex->implode( ', ', $this->Table->permitted( $state, $this->Grammar )) ,"]\n";
			//echo "Stack: [",$this->Lex->implode(', ',$this->stack),"] \n";
			
			// get next action based up on current terminal in input stream
			$n = $this->Table->lookup( $state, $this->t );
			if( is_null($n) ){
				$this->badtoken = $this->tok;
				// display which terminal symbols would be permitted
				$expected = $this->Table->permitted( $state, $this->Grammar );
				if( ! empty($expected) ){
					$expected = 'expecting "'.$this->Lex->implode('", or "',$expected ).'"';
				}
				else {
					$expected = 'no terminals permitted';
				}
				if( isset($this->stack[0]) ){
					$Node = end( $this->stack );
					if( $this->fail('after "%s" %s in state #%u', $this->Lex->name( $Node->scalar_symbol() ), $expected, $state ) ){
						continue;
					}
					else {
						break;
					}
				}
				else if( $this->fail( '%s in state #%u', $expected, $state ) ){
					continue;
				}
				else {
					break;
				}
			}
			
			// Odd numbers are states, even numbers are rules
			if( $n & 1 ){
				// SHIFT
				if( $this->t === P_EOF ){
					$len = count( $this->stack );
					if( $len !== 1 ){
						if( $this->fail('premature EOF stack has %u elements', $len ) ){
							continue;
						}
						else {
							break;
						}
					}
					// Full parse
					return $this->stack[0];
				}
				// shift, and move to next state
				$state = $this->shift( $state, $n );
			}
			else {
				// REDUCE, and move to previous state
				$state = $this->reduce( $state, $n );
			}
		
		}
		
	}
	
	
	
	/**
	 * Perform shift and move to next state
	 * @param int current state
	 * @param int new state
	 * @return int new state
	 */	
	private function shift( $oldstate, $newstate ){
		// prepare node to shift onto stack
		//echo "Shift \"", $this->Lex->name($this->t), "\" to #$newstate\n";	
		$Node = $this->create_node( $this->t );
		$Node->state( $oldstate );
		
		// node must be explicitly terminated
		if( $this->Grammar->is_terminal($this->t) ){
			$Node->terminate( $this->tok );
		}
		// shift and return new state
		$this->stack[] = $Node;

		$this->next_token();
		return $newstate;
	}	
	
	
	
	/**
	 * Perform reduciotn and calculate new state
	 * @param int current state
	 * @param int rule number
	 * @return int new state
	 */	
	private function reduce( $state, $ruleid ){
		// even numbers are reduction rules
		list( $nt, $rhs ) = $this->Grammar->get_rule( $ruleid );
		
		// handle empty string in rule. epsilon will never be pushed onto stack
		if( ! isset($rhs[1]) && $rhs[0] === P_EPSILON ){
			//echo "Reduced to single empty string \n";
			$Node = $this->create_node( $nt );
			$Node->state( $state );
			$this->stack[] = $Node;
			return $this->Table->lookup( $state, $nt );
		}		
		
		//echo "Reduce with rule $ruleid, ", $this->Lex->name($nt), " -> ",$this->implode_tokens($rhs),"\n";
		// the top n nodes removed from the stack must corresond to the right hand side of rule
		$len = count( $rhs );
		$nodes = array_splice( $this->stack, -$len );
		$Node = $this->create_node( $nt );
		// we will adopt the state of the left most symbol - get it now in case of node collapsing in push
		$oldstate = $nodes[0]->state();
		
		// checking is not necessary if we KNOW the table is correct
		// but may as well use the iteration to splice the child nodes
		foreach( $nodes as $i => $childNode ){
			$Node->push( $childNode, $this->recursion );
			//$s = $childNode->scalar_symbol();
			//if( $rhs[$i] !== $s ){
			//	$this->fail('rule (%s->%s) not found on top of stack, found %s', $this->token_name($nt), $this->implode_tokens($rhs), $this->token_name($s) );
			//}
		}
		
		// With the old state from the left most symbol and lookup required next state
		$newstate = $this->Table->lookup( $oldstate, $nt );
		if( is_null($newstate) ){
			$this->fail('no action permitted for (%u,%s)', $oldstate, $this->token_name($nt) );
		}
		$Node->state( $oldstate );
		$this->stack[] = $Node;
		return $newstate;
	}
	
	
	
	
	/**
	 * uncomment to debug
	 * @ignore
	 *
	protected function fail( $extra = '' ){
		// debug - show partial parse trees
		foreach( $this->stack as $i => $Node ){
			echo "partial node [$i]\n";
			$Node->dump( $this->Lex );
		}		
		return parent::fail( '' );
	}
	//*/





}
	
 
 
