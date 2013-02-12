<?php
/**
 * Auto-generated template file containing LR parser class BNFParser 
 * @category PLUG
 * @package parsing
 * @subpackage bnf 
 */	
 
import('PLUG.parsing.LR.LRParser'); 

// include tokenizer
import('PLUG.parsing.bnf.bnf_tokenize');
import('PLUG.parsing.bnf.BNFLex');
import('PLUG.parsing.bnf.BNFGrammar');
import('PLUG.parsing.bnf.BNFParseTable');

// include custom node classes
import('PLUG.parsing.bnf.BNFRulesNode');
import('PLUG.parsing.bnf.BNFRuleNode');
import('PLUG.parsing.bnf.BNFExpressionNode');
import('PLUG.parsing.bnf.BNFListNode');
import('PLUG.parsing.bnf.BNFTermNode');


/**
 * Auto-generated template class for an {@link LRParser} implementation
 * @category PLUG
 * @package parsing
 * @subpackage bnf 
 */
class BNFParser extends LRParser {

	
	/**
	 * Constructor
	 */
	function __construct(){
		// super-construct with Lex and Grammar
		parent::__construct( new BNFLex, new BNFGrammar );
		// set pre-compiled parse table
		$this->Table = new BNFParseTable;
		// register custom parse nodes ...
		$this->register_node_class( BNF_RULES, 'BNFRulesNode' );
		$this->register_node_class( BNF_RULE, 'BNFRuleNode' );
		$this->register_node_class( BNF_EMPTY_RULE, 'BNFRuleNode' );
		$this->register_node_class( BNF_EXPRESSION, 'BNFExpressionNode' );
		$this->register_node_class( BNF_LIST, 'BNFListNode' );
		$this->register_node_class( BNF_TERM, 'BNFTermNode' );		
	}


	
	/**
	 * parse input string
	 * @param string input source
	 * @return ParseNode
	 */
	static function parse_string( $s ){
		if( $s == '' ){
			throw new Exception('Cannot parse empty string');
		}
		// instantiate self
		$Parser = new BNFParser;
		// perform external tokenizing on string input 
		$tokens = bnf_tokenize( $s );
		return $Parser->parse( $tokens );
	}

	

	/**
	 * Parse input from file
	 * @param string file path
	 * @return ParseNode
	 */
	static function parse_file( $f ){
		$src = file_get_contents( $f, true );
		if( $src === false ){
			throw new Exception("Cannot read file $f");
		}
		return self::parse_string( $src );
	}

}