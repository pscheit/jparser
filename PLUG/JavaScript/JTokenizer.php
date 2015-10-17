<?php

namespace PLUG\JavaScript;

/**
 * File containing class JTokenizer
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @version $Id: JTokenizer.php,v 1.1 2009/03/22 12:51:43 twhitlock Exp $
 */


use PLUG\JavaScript\JLex;
use PLUG\JavaScript\JTokenizerBase;
use PLUG\parsing\Lex;


/**
 * @category PLUG
 * @package JavaScript
 */
class JTokenizer extends JTokenizerBase {
	
	/**
	 * @override
	 */
	protected $regPunc = '/(?:\>\>\>\=|\>\>\>|\<\<\=|\>\>\=|\!\=\=|\=\=\=|&&|\<\<|\>\>|\|\||\*\=|\|\=|\^\=|&\=|%\=|-\=|\+\+|\+\=|--|\=\=|\>\=|\!\=|\<\=|;|,|\<|\>|\.|\]|\}|\(|\)|\[|\=|\:|\||&|-|\{|\^|\!|\?|\*|%|~|\+)/';

	
	/**
	 * Constructor
	 */	
	function __construct( $whitespace, $unicode ){
		parent::__construct( $whitespace, $unicode );
		$this->Lex = Lex::get(JLex::class);
	}		
		
	
}
