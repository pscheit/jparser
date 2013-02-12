<?php
/**
 * File containing function PLUG.JavaScript.j_token_get_all
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @version $Id: j_token_get_all.php,v 1.1 2009/03/22 12:51:43 twhitlock Exp $
 */


/**
 * Require JavaScript Tokenizer
 */
import('PLUG.JavaScript.JTokenizer');




/**
 * Tokenize Javascript/ECMAScript source text.
 * @example JavaScript/j_token_get_all.php
 * @param string source text
 * @param bool optionally specify whether to include whitespace and comments. Note that line terminators are always included
 * @param bool whether to fully support Unicode
 * @return array terminal symbols as array tokens
 */	
function j_token_get_all( $src, $whitespace = true, $unicode = true ){
	$Tokenizer = new JTokenizer( $whitespace, $unicode );
	return $Tokenizer->get_all_tokens( $src );
}

