<?php

namespace PLUG\parsing;

/**
 * File containing class Parser
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @version $Id: Parser.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */

use Exception;
use PLUG\parsing\ParseError;
use PLUG\parsing\ParseNode;

 
/**
 * Abstract Parser class.
 * Extend this class with algorithmic logic to implement a parser.
 * @category PLUG
 * @package parsing
 */
abstract class Parser {

	/**
	 * A Lex instance is required for knowledge of lexicon
	 * @var object Lex
	 */
	protected $Lex;

	/**
	 * A Grammar instance is required for knowledge of grammar
	 * @var object Grammar
	 */
	protected $Grammar;
	 
	/**
	 * Registry of custom parse node classes
	 * @var array
	 */	 
	protected $node_classes = array();
	
	/**
	 * @var string
	 */
	protected $default_node_class = ParseNode::class;
	
	/**
	 * input token stream
	 * @var array
	 */
	protected $input;
	
	/**
	 * current input token
	 * @var Mixed
	 */
	protected $tok;	
	
	/**
	 * current target scalar input symbol
	 * @var int
	 */
	protected $t;	

	/**
	 * Last unexpected input token
	 * @var mixed
	 */
	protected $badtoken;
	
	/**
	 * Whether to keep recursive nesting of nodes, or remove during parsing, defaults to false
	 * @var bool
	 */
	protected $recursion = false;

	/**
	 * Super constructor
	 * Must be called from descendants to ensure parser has knowledge of Lex and Grammar
	 * @param Lex
	 * @param Grammar
	 */		
	protected function __construct( Lex $Lex, Grammar $Grammar ){
		$this->Lex = $Lex;
		$this->Grammar = $Grammar;
	}		
	

	
	/**
	 * @param array input token stream
	 * @return object
	 */
	abstract function parse( array $input );
	
	
	
	/**
	 * Initialize parser prior to starting parse
	 * @var array
	 * @return void
	 */
	protected function init( array $input ){
		// disallow empty streams
		if( empty( $input ) ){
			throw new Exception('Input stream is empty');
		}
		// initialize input stream
		$this->input = $input;
		$this->input[] = P_EOF;
		$this->current_token();
		// check we were constructed properly by descendant
		if( ! isset($this->Lex) || ! $this->Lex instanceof Lex ){
			throw new Exception('Parser does not know about Lex');
		}
		if( ! isset($this->Grammar) || ! $this->Grammar instanceof Grammar ){
			throw new Exception('Parser does not know about Grammar');
		}
	}



	/**
	 * Very simple tokenizer.
	 * Splits string into single character input tokens.
	 * - ignores line breaks (LF only)
	 * @param string
	 * @return array
	 */
	function tokenize( $src ){
		// perform very simple tokenizing on string input
		$tokens = array();
		$chars = preg_split('//', $src, -1, PREG_SPLIT_NO_EMPTY );
		$line = 1;
		$col = 1;
		foreach( $chars as $i => $chr ){
			switch( $chr ){
			case "\n":
				$line ++;
				$col = 1;
				break;
			default:
				$tokens[] = array( $chr, $chr, $line, $col++ );
			}
		}
		return $tokens;
	}

	
	
	
	/**
	 * Throw a parse error
	 * @internal
	 * @param string specific description of precise fail reason
	 * @param ...
	 */	
	protected function fail( $extra = '' ){
	
		if( $extra && func_num_args() > 1 ){
			$args = func_get_args();
			$extra = call_user_func_array( 'sprintf', $args );
		}
		
		// gather information about failure point
		if( is_null( $this->badtoken ) ){
			// unexpected, show current token as failure point
			$tok = $this->tok;
		}
		else {
			// known last failure
			$tok = $this->badtoken;
		}	
		if( is_array($tok) ){
			$tokenName = $this->token_name($tok[0]);
			$tokenSymbol = $tok[0];
			// show line number if possible
			if( isset($tok[2]) ){
				$tokenLine = $tok[2];
			}
			else {
				$tokenLine = null;
			}
			// show column number if possible
			if( isset($tok[3]) ){
				$tokenColumn = $tok[3];
			}
			else {
				$tokenColumn = null;
			}
		}
		else if( is_scalar($tok) ){
			// not very useful, only symbol is known
			$tokenName = $this->token_name($tok);
			$tokenSymbol = $tok;
			$tokenLine = 0;
			$tokenColumn = 0;
		}
		throw new ParseError( $extra, 0, $tokenLine, $tokenColumn, $tokenName, $tokenSymbol );
	}

	
	
	/**
	 * Register a custom ParseNode class for a given symbol
	 * @param Mixed
	 * @param string
	 * @return void
	 */
	function register_node_class( $s, $class ){
		if( ! class_exists($class) ){
			$s = $this->token_name( $s );
			throw new Exception( "If you want to register class `$class' for symbol `$s' you should include it first" );
		}
		$this->node_classes[$s] = $class;
	}	
	
	
	
	
	/**
	 * Create a Parse Node according to registered symbol
	 * @param mixed scalar symbol
	 * @return ParseNode
	 */	
	function create_node( $s ){
		if( isset($this->node_classes[$s]) ){
			// custom node class
			$class = $this->node_classes[$s];
		}	
		else {
			// vanilla flavour node
			$class = $this->default_node_class;
		}
		return new $class( $s );
	}
	
	
	
	
	/**
	 * Reduce complex token to scalar symbol
	 * @param mixed
	 * @return mixed
	 */	
	static function token_to_symbol( $t ){
		if( is_array($t) ){
			// PHP Tokenizer style array token
			$t = $t[0];
		}
		return $t;
	} 	
	
	
	
	
	/**
	 * Get name of scalar symbol for debugging
	 * @param Mixed 
	 * @return string
	 */
	function token_name ( $t ){
		$t = self::token_to_symbol( $t );
		if( ! is_int($t) ){
			return $t;
		}
		return $this->Lex->name( $t );
	}
	
	
	
	/**
	 * Print basic information about a token for debugging
	 * @param Mixed
	 * @param bool optionally return string instead of echo
	 * @return string or 1 if $r is set
	 */
	function print_token( $t, $r = false ){
		if( is_scalar($t) ){
			// already a scalar symbol
			$s = $this->token_name( $t );
		}
		else if( is_array( $t ) ){
			if( $t[0] && $t[0] !== $t[1] ){
				// [ symbol, value ]
				$s = sprintf( '%s="%s"', $this->token_name($t[0]), $t[1] );
			}
			else {
				// [ empty, value ]
				$s = sprintf('"%s"', $t[1] );
			}
		}
		else {
			$s = 'ERROR';
		}
		return $r ? $s : print($s);
	}
	


	
	/**
	 * Debugging func
	 * @ignore
	 * @override
	 */
	function implode_tokens( array $a ){
		$b = array();
		foreach( $a as $token ){
			$b[] = $this->token_name( $token );
		}
		return implode( ' ', $b );
	}	
	
	
	
	
	
	/**
	 * Debugging function
	 */
	static function dump_tokens( $tokens ){
		$line = 0;
		foreach( $tokens as $token ){
			
			list($t,$s,$l,$c) = $token;
			
			if( $l !== $line ){
				$line = $l;
				echo "#$line :\n";
			}
			if( $s === $t ){
				// literal
				echo "   $s\n";
			}
			else {
				echo "  ", $this->token_name($t), " = $s\n";
			}
		}
	}
	
		
	
	/**
	 * Get current token from input stream.
	 * @return Mixed 
	 */	
	protected function current_token(){
		if( !isset($this->tok) ){
			$this->tok = current( $this->input );
			$this->t = self::token_to_symbol( $this->tok );
		} 
		return $this->tok;
	}


	
	
	/**
	 * Get scalar input symbol from current input token
	 * @return int
	 */
	protected function current_input_symbol(){
		if( !isset($this->t) ){
			$this->current_token();
		}
		return $this->t;
	}	
	
	
	
	
	/**
	 * Advance input stream to next token
	 * @return Mixed next token in input stream
	 */
	protected function next_token(){
		$this->tok = next( $this->input );
		$this->t = self::token_to_symbol( $this->tok );
		return $this->tok;
	}
	
	
	
	
	/**
	 * Rollback input stream to previous token
	 * @return Mixed previous token in input stream
	 */
	protected function prev_token(){
		$this->tok = prev( $this->input );
		$this->t = self::token_to_symbol( $this->tok );
		return $this->tok;
	}
	
	
	
	
}
 
 
