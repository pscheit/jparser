<?php

namespace PLUG\JavaScript;

/**
 * File containing function j_token_name
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @version $Id: j_token_name.php,v 1.1 2009/03/22 12:51:43 twhitlock Exp $
 */


/**
 * Require JavaScript Lexicon
 */
use PLUG\JavaScript\JLex;
use PLUG\parsing\Lex;

/**
 * Get name of Javascript terminal symbol
 * @param int scalar symbol
 * @return string
 */	
function j_token_name( $t ){
	$Lex = Lex::get(JLex::class);
	return $Lex->name( $t );
}