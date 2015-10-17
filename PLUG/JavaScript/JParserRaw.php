<?php

namespace PLUG\JavaScript;

/**
 * File containing class JParserRaw
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @version $Id: JParserRaw.php,v 1.1 2009/03/22 12:51:43 twhitlock Exp $
 */

 
use PLUG\JavaScript\JParserBase; 
use PLUG\JavaScript\JTokenizer; 
use PLUG\JavaScript\JLex; 
use PLUG\JavaScript\JGrammar; 
use PLUG\JavaScript\JParseTable; 

 
/**
 * Full JavaScript parser maintains full parse tree with no custom nodes
 * @category PLUG
 * @package JavaScript
 */
class JParserRaw extends JParserBase {

	/**
	 * Constructor
	 */
	function __construct(){
		// super-construct with Lex and Grammar
		parent::__construct( new JLex, new JGrammar );
		// set pre-compiled parse table
		$this->Table = new JParseTable;
	}
	
	
	/**
	 * @override
	 */
	static function parse_string( $src, $unicode = true, $parser = __CLASS__, $lexer = 'JTokenizer' ){
		return parent::parse_string( $src, $unicode, $parser, $lexer );
	}
	
}
	
 
 
