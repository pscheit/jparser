<?php
/**
 * File containing class IncParser
 * @category PLUG
 * @package compiler 
 * @subpackage miniparsers 
 */	
 
import('PLUG.PHP.PHPParserBase'); 
import('PLUG.compiler.miniparsers.Inc.IncLex');
import('PLUG.compiler.miniparsers.Inc.IncGrammar');
import('PLUG.compiler.miniparsers.Inc.IncParseTable');
import('PLUG.compiler.miniparsers.Inc.IncStatementNode');
import('PLUG.compiler.miniparsers.Inc.IncArgumentNode');


/**
 * Include statement miniparser.
 * This is used by the PLUG compiler to evaluate include statements that may contain variable arguments.
 * Only a very limited range of expressions are supported by the grammar.
 * Currently you may only concatenate string literals and constants.
 * @todo support variables as well as constants, other punctuators than just '.', other data types than just string
 * @category PLUG
 * @package compiler 
 * @subpackage miniparsers 
 */
class IncParser extends PHPParserBase {

	/**
	 * Constructor
	 */
	function __construct(){
		// super-construct with Lex and Grammar
		parent::__construct( new IncLex, new IncGrammar );
		// set pre-compiled parse table
		$this->Table = new IncParseTable;
		// register custom parse nodes ...
		$this->register_node_class( NT_STATEMENT, 'IncStatementNode' );
		$this->register_node_class( NT_ARG, 'IncArgumentNode' );
	}
	
	
	
	/**
	 * @override
	 * strip out insignificant tokens
	 * @todo perhaps overload Parser::next_token instead
	 
	function parse_string( $src ){
		$significant = array();
		foreach( $tokens as $tok ){
			if( is_array($tok) ){
				switch( $tok[0] ){
				case T_COMMENT:
				case T_DOC_COMMENT:
				case T_WHITESPACE:
					continue 2;
				}
			}
			$significant[] = $tok;
		}
		return parent::parse( $significant );
	}*/	



	
	
	/**
	 * @param string
	 */	
	static function parse_string( $src ){
		// src must begin with an opening php tag to tokenize properly
		if( substr($src,0,2) !== '<?' ){
			$src = "<?php\n" . $src;
		}
		// use native tokenizer
		$tokens = token_get_all( $src );
		// remove opening tag while also checking for tokenizer bug
		$opentag = array_shift( $tokens );
		if( $opentag[0] !== T_OPEN_TAG ){
			trigger_error("Tokenizer error: T_OPEN_TAG expected, got ".print_r($opentag,1), E_USER_ERROR );
		}
		// construct and parse
		$Parser = new IncParser();
		return $Parser->parse( $tokens );
	}	
	
	
	
	
	
	

}
