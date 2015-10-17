<?php

namespace PLUG\JavaScript;

/**
 * File containing class JParserBase
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @version $Id: JParserBase.php,v 1.1.2.1 2009/11/14 16:27:56 twhitlock Exp $
 */

 
use PLUG\parsing\LR\LRParseNode;
use PLUG\parsing\LR\LRParser;
//use PLUG\JavaScript\j_token_get_all; 

 
/**
 * Abstract base class to be extended by a parser implementation for any ECMAScript based language.
 * @category PLUG
 * @package JavaScript
 */
abstract class JParserBase extends LRParser {


	/**
	 * State indicating whether the current token was separated from the previous token by at least one line terminator
	 * @var bool
	 */
	private $newline = false;
	
	/**
	 * Token indicating where semicolon insertion was performed to remedy a parse error.
	 * If this same token appears twice in a row the parser will admit defeat.
	 * @var array
	 */
	private $asitoken;
	
	
	/**
	 * Parse a JavaScript string according to specific parser class
	 * @param string source string
	 * @param string parser subclass to use
	 * @return \PLUG\parsing\LR\LRParseNode
	 */
	static function parse_string( $src, $unicode = true, $parser = __CLASS__, $lexer = 'JTokenizer' ){
		$Tokenizer = new $lexer( false, $unicode);
		$tokens = $Tokenizer->get_all_tokens( $src );
		unset( $src );
		$Parser = new $parser;
		return $Parser->parse( $tokens );
	}
	

	
	/**
	 * @override
	 */	
	protected function current_token(){
		$t = parent::current_token();
		if (
			$t[0] === J_LINE_TERMINATOR
		){
			$t = $this->next_token();
			$this->newline = true;
		}
		return $t;
	}
	
	
	
	/**
	 * @override
	 */
	protected function next_token(){
		$s = $this->t;
		$this->newline = false;
		do {
			$tok = parent::next_token();
			if( ! $tok ){
				parent::fail('Failed to get next token');
			}
		} 
		while ( 
			$tok[0] === J_LINE_TERMINATOR && $this->newline = true
		);
		if( $this->newline ){
			// Handle [no LineTerminator here] rules:
			// When, as the program is parsed from left to right, a token is encountered that is allowed by some 
			// production of the grammar, but the production is a restricted production and the token would be the 
			// first token for a terminal or nonterminal immediately following the annotation "[no LineTerminator here]" 
			// within the restricted production (and therefore such a token is called a restricted token), and 
			// the restricted token is separated from the previous token by at least one LineTerminator, then a 
			// semicolon is automatically inserted before the restricted token.
			switch( $s ){
			case J_CONTINUE:
			case J_BREAK:
			case J_RETURN:
				$this->insert_semicolon();
				return $this->tok;
			case J_THROW:
				// "throw ;" is invalid
				return parent::fail('No line terminator after %s', $this->token_name($s) );
			}
			// Ensure that Postfix operators are not grouped with preceeding symbol;
			// They must instead end up as a Unary expression if there was a line break
			if( $s !== ';' && ( $tok[0] === '++' || $tok[0] === '--' ) ){
				$this->insert_semicolon();
				return $this->tok;
			}
		}
		return $tok;
	}
	
	
	
	
	/**
	 * @override
	 */
	protected function prev_token(){
		do {
			$t = parent::prev_token();
		} 
		while ( 
			$t[0] === J_LINE_TERMINATOR 
		);
		return $t;
	}
	
	
	
	
	/**
	 * Failure handler
	 * @param string
	 * @param ...
	 * @return bool whether to continue parsing
	 */
	protected function fail( $extra = '' ){
		// how to call parent function with arbitrary arguments?
		// - we can't so we'll do the sprintf here
		if( $extra && func_num_args() > 1 ){
			$args = func_get_args();
			$extra = call_user_func_array( 'sprintf', $args );
		}
		// ECMAScript Edition 3 - 7.9.1 Automatic Semicolon Insertion
		do { 
			
			// Check that wer're not choking on an automatically inserted semicolon
			if( $this->t == ';' && $this->tok[3] === 0 ){
				$this->badtoken = $this->asitoken;
			}
			
			// Overriding rules:
			// @todo need to test partial node trees to see if we are in a J_FOR statement
			// --
			
			// When, as the program is parsed from left to right, a token (called the offending token) is encountered 
			// that is not allowed by any production of the grammar, then a semicolon is automatically inserted 
			// before the offending token if one or more of the following conditions is true:
	
			// 1. The offending token is separated from the previous token by at least one LineTerminator.
			if( $this->newline ){
				return  $this->insert_semicolon( $extra );
			}
			
			// 2. The offending token is }.
			if( $this->t === '}' ){
				return $this->insert_semicolon( $extra );
			}
			
			// When, as the program is parsed from left to right, the end of the input stream of tokens is 
			// encountered and the parser is unable to parse the input token stream as a single complete 
			// ECMAScript Program, then a semicolon is automatically inserted at the end of the input stream.
			if( $this->t === P_EOF ){
				return $this->insert_semicolon( $extra );
			}
		}
		while( false );
		return parent::fail( $extra );
	}
	
	
	
	
	
	/**
	 * Force a semicolon into the token stream before the current token and rewind to that point.
	 * In fact we are only pretending there was a semicolon. as we should not need to rewind.
	 * This is quicker as splicing the input is problematic due to the internal array pointer.
	 * @return bool
	 */
	private function insert_semicolon( $failtext = null ){
		// Remember failing token in case we insert a semicolon and it fails again
		if( isset($failtext) && isset($this->badtoken) ){
			if( isset($this->asitoken) && $this->asitoken === $this->badtoken ){
				return parent::fail( $failtext );
			}
			$this->asitoken = $this->badtoken;
		}
		// insert semilcolon after current token
		$prevtok = $this->prev_token();
		if( ! $prevtok ){
			parent::fail( $failtext );
		}
		// placing semicolon at imaginary column 0
		$this->tok = array( ';', ';', $prevtok[2], 0 );
		$this->t = ';';
		$this->newline = false;
		return true;
	}
	
	
	
	
	
	
}
	
 
 
