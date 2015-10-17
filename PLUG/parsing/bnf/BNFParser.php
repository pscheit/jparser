<?php

namespace PLUG\parsing\bnf;

/**
 * Auto-generated template file containing LR parser class BNFParser 
 * @category PLUG
 * @package parsing
 * @subpackage bnf 
 */	

use Exception;
use PLUG\parsing\LR\LRParser; 

// include tokenizer
use function PLUG\parsing\bnf\bnf_tokenize;
use PLUG\parsing\bnf\BNFLex;
use PLUG\parsing\bnf\BNFGrammar;
use PLUG\parsing\bnf\BNFParseTable;

// include custom node classes
use PLUG\parsing\bnf\BNFRulesNode;
use PLUG\parsing\bnf\BNFRuleNode;
use PLUG\parsing\bnf\BNFExpressionNode;
use PLUG\parsing\bnf\BNFListNode;
use PLUG\parsing\bnf\BNFTermNode;


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
		$this->register_node_class( BNF_RULES, BNFRulesNode::class );
		$this->register_node_class( BNF_RULE, BNFRuleNode::class );
		$this->register_node_class( BNF_EMPTY_RULE, BNFRuleNode::class );
		$this->register_node_class( BNF_EXPRESSION, BNFExpressionNode::class );
		$this->register_node_class( BNF_LIST, BNFListNode::class );
		$this->register_node_class( BNF_TERM, BNFTermNode::class );
	}


	
	/**
	 * parse input string
	 * @param string input source
	 * @return \PLUG\parsing\ParseNode
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
	 * @return \PLUG\parsing\ParseNode
	 */
	static function parse_file( $f ){
		$src = file_get_contents( $f, true );
		if( $src === false ){
			throw new Exception("Cannot read file $f");
		}
		return self::parse_string( $src );
	}

}