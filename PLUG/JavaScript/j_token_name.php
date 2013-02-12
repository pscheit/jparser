<?php
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
import('PLUG.JavaScript.JLex');


/**
 * Get name of Javascript terminal symbol
 * @param int scalar symbol
 * @return string
 */	
function j_token_name( $t ){
	$Lex = Lex::get('JLex');
	return $Lex->name( $t );
}