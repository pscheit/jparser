<?php

namespace PLUG\parsing;

/**
 * File containing class ParseNode
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @version $Id: ParseNode.php,v 1.1.2.1 2009/11/14 16:27:56 twhitlock Exp $
 */

 
 
/**
 * ParseNode base class.
 * Custom nodes may overload this and be registered in the parser against particular terminal and nonterminal symbols.
 * @example parsing/ParseNode.php
 * @category PLUG
 * @package parsing
 */
class ParseNode {

	/**
	 * registry of all nodes indexed by idx
	 * @var array
	 */
	private static $reg = array();	
	
	/**
	 * incremental value for node index
	 * @var int
	 */	
	private static $i = 0;	

	/**
	 * numerical index of this node in registry
	 * @var int
	 */	
	private $idx;
	
	/**
	 * numerical index of this node's parent in registry
	 * @var int
	 */	
	private $pidx;
	
	/**
	 * Numerically indexed children
	 * @var array
	 */
	protected $children = array();
	
	/**
	 * @var int
	 */
	protected $length = 0;
	
	/**
	 * Internal pointer, allows calls to end() etc, without interupting real array pointer in $children
	 * @var int
	 */	
	private $p;
		
	/**
	 * Scalar symbol constant that this node relates to
	 * @var Mixed
	 */
	protected $t;
	
	/**
	 * Line number of teminal token if available
	 * @var int
	 */
	protected $l;
		
	/**
	 * Column number of teminal token if available
	 * @var int
	 */
	protected $c;
	
	/**
	 * Value of terminal token when a leaf node
	 * @var string
	 */
	protected $value;
	
	/**
	 * @var int
	 */	
	var $depth = 0;	
	
	/**
	 * Whether to maintain instances of self inside self.
	 * If set, this overrides the parameter passed to $this->push.
	 * @var bool
	 */
	protected $recursion;
	
	
	/**
	 * Get a single node from the registry by it's ID
	 * @param int
	 * @return ParseNode
	 */
	static function get( $idx ){
		return self::$reg[$idx];
	}	
	

	
	
	/**
	 * Constructor
	 * @param Mixed scalar symbol id
	 * @param int optional rule index stored during parsing
	 */
	function __construct( $t ){
		$this->t = $t;
		// register self globally
		$this->idx = self::$i++;
		self::$reg[$this->idx] = $this;
	}	
	
	
	
	/**
	 * Explicit destructor.
	 * We cannot rely on __destruct because the static registry means destruction will never naturally occur
	 * @return ParseNode parent node if exists or null
	 */
	function destroy(){
		while( $Child = $this->pop() ){
			$Child->destroy();
		}	
		$Parent = $this->get_parent() and $Parent->remove( $this );
		unset( self::$reg[$this->idx] );
		$this->idx = null;
		return $Parent;
	}	
	
	
	/**
	 * Clean up function
	 * @return void
	 */
	static function destroy_all(){
		self::$reg = array();
		self::$i = 0;
	}
	
	
	
	/**
	 * recursively get last element in entire tree
	 * @return ParseNode
	 */
	function abs_end(){
		$Node = $this;
		while( $Child = end( $Node->children ) ){
			$Node = $Child;
		}
		return $Node;
	}
	
	
	
	
	/**
	 * Recursively pop last element from extremity tree
	 * @return ParseNode
	 */
	function abs_pop(){
		$Node = $this->abs_end();
		if( is_null($Node->pidx) ){
			// cannot pop self from self
			return false;
		}
		// pop node from it's parent
		return $Node->get_parent()->pop();
	}		




	/**
	 * get/set the scalar symbol of this node's token
	 * @param int|string optional symbol to set
	 * @return int|string
	 */
	function scalar_symbol( $s = null ){
		$t = $this->t;
		if( !is_null($s) ){
			$this->t = $s;
		}
		return $t;
	}	
	
	
	
	
	/**
	 * Terminate this node with a terminal symbol or token
	 * @param Mixed
	 * @return void
	 */
	function terminate( $tok ){
		if( is_scalar($tok) ){
			// scalar token e.g. ";"
			if( is_null($this->t) ){
				$this->t = $tok;
			}
			$this->value = $tok;
		}
		else if( is_array($tok) ){
			// PHP tokenizer style array token
			$this->t = $tok[0];
			$this->value = $tok[1];
			// store additional info in terminal node
			if( isset($tok[2]) ){
				$this->l = $tok[2];
				if( isset($tok[3]) ){
					$this->c = $tok[3];
				}
			}
		}
		// destroy all children
		while( $Child = $this->pop() ){
			$Child->destroy();
		}
	}	
	
	
	
	/**
	 * Get child at given index
	 * @param int
	 * @return ParseNode
	 */
	function get_child( $i ){
		return isset($this->children[$i]) ? $this->children[$i] : null;
	}	
	
	
	
	/**
	 * Get line number.
	 * This will search recursively until a terminal node is reached.
	 * @return int
	 */
	function get_line_num(){
		if( ! isset($this->l) ){
			if( isset($this->children[0]) ){
				$this->l = $this->children[0]->get_line_num();
			}
			else {
				$this->l = 0;
			}
		}
		return $this->l;
	}	
	
	
	
	/**
	 * Get column number.
	 * This will search recursively until a terminal node is reached.
	 * @return int
	 */
	function get_col_num(){
		if( ! isset($this->c) ){
			if( isset($this->children[0]) ){
				$this->c = $this->children[0]->get_col_num();
			}
			else {
				$this->c = 0;
			}
		}
		return $this->c;
	}
	
	
	
	/**
	 * Append a child node immediately onto self
	 * @param ParseNode
	 * @param bool whether to keep recursive nesting, or remove on the fly, defaults to true
	 * @return int new length of this node
	 */	
	function push( ParseNode $Node, $recursion = true ){
		if( $Node->pidx ){
			trigger_error("Node $Node->idx already has parent $Node->pidx", E_USER_WARNING );
		}
		// Resolve recursion on the fly if required
		if( $this->t === $Node->t && $Node->length ){
			// allow node's own setting to override parameter
			if( isset($Node->recursion) ){
				$recursion = $Node->recursion;
			}
			if( ! $recursion ){
				$this->push_thru( $Node );
				$Node->destroy();
				return $this->length;
			}
		}
		// else set node as child of self
		$Node->pidx = $this->idx;
		$Node->depth = $this->depth + 1;
		$this->p = 0;
		return $this->length = array_push( $this->children, $Node );	
	}
	
	
	
	/**
	 * Push the child nodes of a node onto this node
	 * @param ParseNode
	 * @return int new length of this node
	 */
	function push_thru( ParseNode $Node ){
		foreach( $Node->children as $Child ){
			$Node->remove( $Child );
			$this->push( $Child );
		}
		return $this->length;
	}
	
	
	/**
	 * Remove immediate child from the end of this node
	 * @return ParseNode
	 */	
	function pop(){
		if( ! $this->length ){
			return null;
		}
		if( --$this->length <= 0 ){
			$this->length = 0;
			$this->p = null;
		}
		else {
			$this->p = 0;
		}
		$Node = array_pop( $this->children );
		$Node->pidx = null;
		$Node->depth = 0;
		return $Node;
	}	
	
	
	
	/**
	 * Remove passed child node from this node
	 * @param ParseNode 
	 * @return ParseNode
	 */
	function remove( ParseNode $Node ){
		foreach( $this->children as $i => $Child ){
			if( $Child->idx === $Node->idx ){
				return $this->remove_at( $i );
			}
		}
	}	

	

	/**
	 * Remove child node at known index from this node
	 * @param int
	 * @return ParseNode
	 */
	function remove_at( $i ){
		$Child = $this->children[$i];
		$Child->pidx = null;
		$Child->depth = 0;
		array_splice( $this->children, $i, 1 );
		if( ! --$this->length ){
			$this->p = null;
		}
		else {
			$this->p = 0;
		}
		return $Child;
	}	
	
	
	
	
	/**
	 * Replace child node with other nodes
	 * @param ParseNode node to replace
	 * @param array replacement
	 * @param ParseNode 
	 */
	function splice( ParseNode $Node, array $nodes ){
		foreach( $this->children as $i => $Child ){
			if( $Child->idx === $Node->idx ){
				return $this->splice_at( $i, $nodes, 1 );
			}
		}	
	}
	
	
	
	/**
	 * Replace child node at known index with other nodes
	 * @param int
	 * @param array replacement
	 * @param ParseNode child node removed
	 */
	function splice_at( $i, array $nodes, $len = 0){
		$Child = $this->children[$i];
		$Child->pidx = null;
		$Child->depth = 0;
		array_splice( $this->children, $i, $len, $nodes );
		foreach( $nodes as $Node ){
			$Node->pidx = $this->idx;
		}
		$this->length = count( $this->children );
		$this->p = 0;
		return $Child;
	}
	
	
	
	/**
	 * Proxy array access function
	 * @return ParseNode
	 */
	function end(){
		if( ! $this->length ){
			return false;
		}
		$Child = end( $this->children );
		$this->p = key( $this->children );
		return $Child;
	}	
	
	

	
	/**
	 * Proxy array access function
	 * @return ParseNode
	 */
	function reset(){
		if( ! $this->length ){
			return false;
		}
		$this->p = 0;
		return $this->children[0];
	}	
	
	
	
	/**
	 * Proxy array access function
	 * @return ParseNode
	 */
	function current(){
		if( ! $this->length ){
			return false;
		}
		return $this->children[ $this->p ];
	}	
	
	
	
	/**
	 * Proxy array access function
	 * @return ParseNode
	 */
	function prev(){
		if( ! $this->length ){
			return false;
		}
		$p = $this->p - 1;
		if( !isset($this->children[$p]) ){
			$this->p = 0;
			return false;
		}
		$this->p = $p;
		return $this->children[$p];
	}	
	
	
	
	/**
	 * Proxy array access function
	 * @return ParseNode
	 */
	function next(){
		if( ! $this->length ){
			return false;
		}
		$p = $this->p + 1;
		if( !isset($this->children[$p]) ){
			$this->p = 0;
			return false;
		}
		$this->p = $p;
		return $this->children[$p];
	}	
	
	
	
	
	/**
	 * Proxy array access function
	 * @return int
	 */
	function key(){
		return $this->p;
	}
	
	


	/**
	 * Get this node's parent
	 * @return ParseNode
	 */
	function get_parent(){
		if( !is_null($this->pidx) && isset(self::$reg[$this->pidx]) ){
			return self::$reg[$this->pidx];
		}
		else {
			return null;
		}
	}

	
	
	
	/**
	 * Test whether node is terminal.
	 * i.e. that it is a leaf node with a terminal symbol value
	 * @return bool
	 */
	function is_terminal(){
		return ! is_null( $this->value );
	}	
	

	
	/**
	 * Test whether node is a given symbol
	 * @param int|string
	 * @return bool
	 */
	function is_symbol( $t ){
		return $this->t === $t;
	}

	
	
	/**
	 * Test whether node had any child nodes
	 * @return bool
	 */
	function has_children(){
		return $this->length !== 0;
	}	
	
	

	/**
	 * Recursively fetch nodes by symbol
	 * @param int scalar symbol
	 * @param int optional recursion limit, 0 for no recursion, null for unlimited
	 * @param array optional list of symbols that should not be descended into
	 * @return array
	 */
	function get_nodes_by_symbol( $t, $dmax = null, array $blocklist = null, $d = 0 ){
		$a = array();
		if( ! is_null($dmax) && $d > $dmax ){
			return $a;
		}
		if( $this->t === $t ){
			$a[] = $this;
		}
		// don't descend into list of blocked symbols, ignoring self if at depth 0.
		if( $d && $blocklist && in_array( $this->t, $blocklist, true) ){
			return $a;
		}
		// else recurse into child nodes
		$d ++;
		foreach( $this->children as $Child ){
			$a = array_merge( $a, $Child->get_nodes_by_symbol( $t, $dmax, $blocklist, $d ) );
		}
		return $a;
	}



	
	/**
	 * Overloadable method to evaluate this node and it's contents.
	 * For example a node <SUM> with children [NUM,NUM] may add and return the values of the child nodes.
     * Non-terminal nodes will return an array of child node values
	 * @return Mixed
	 */
	function evaluate(){
		if( $this->is_terminal() ){
			return $this->value;
		}
		if( $this->t === P_EPSILON ){
			return null;
		}
		// collect array of child values
		$values = array();
		$Child = $this->reset();
		do {
			$value = $Child->evaluate();
			if( ! is_null($value) ){
				$values[] = $value;
			}
		}
		while( $Child = $this->next() );
		return $values;
	}
	
	
	
	
	/**
	 * Overloadable method returns literal string value of symbol
	 * @return string
	 */
	function __toString(){
		if( $this->is_terminal() ){
			return (string) $this->value; 
		}
		// else concat child node string values
		$s = '';
		foreach( $this->children as $Child ){
			$s .= $Child->__toString();
		}
		return $s;
	}	
	
	
	
	
	/**
	 * Export tree as array.
	 * Terminal nodes become tokens, non terminals branches.
	 * @return array
	 */
	function export(){
		if( $this->is_terminal() ){
			if( $this->t === $this->value ){
				// scalar token
				return $this->value;
			}
			// PHP Tokenizer style array
			return array( $this->t, $this->value );
		}
		// else is a branch
		$a = array();
		foreach( $this->children as $Child ){
			$a[] = array( $this->t, $Child->export() );
		}
		return $a;
	}
	
	
	
	/**
	 * Resolve recursion.
	 * This is required to make linear sense of repeating/recursive rules which nest as a natural result of the grammar.
	 * e.g. [ RULES ::= RULE | RULE,RULES ] resolves to [ RULES ::= RULE,RULE,RULE,... ].
	 * @return void
	 */
	function resolve_recursion(){
		$copy = $this->children;
		foreach( $copy as $i => $Child ){
			// recurse.
			$Child->resolve_recursion();
			// if child node is same symbol as self, replace with it's contents
			if( $Child->t === $this->t ){
				$this->splice( $Child, $Child->children );
			}
		}
	}	

	
	
	/**
	 * Debugging function to inspect the node tree
	 * @return void
	 */
	function dump( Lex $Lex, $tab = '' ){
		$tag = $Lex->name( $this->t );
		if( $this->is_terminal() ){
			if( $this->value && $this->value !== $this->t ){
				echo $tab, '<',$tag,">\n   ", $tab, htmlspecialchars($this->value),"\n",$tab,'</',$tag,">\n";
			}
			else {
				echo $tab, htmlspecialchars($this->value),"\n";
			}
		}
		else if( ! $this->length ){
			echo $tab, '<', $tag, " />\n";
		}
		else {
			echo $tab, '<',$tag,">\n";
			foreach( $this->children as $Child ){
				$Child->dump( $Lex, "   ".$tab);
			}
			echo $tab, '</', $tag, ">\n";
		}
	}	
	
	
}
	
 
