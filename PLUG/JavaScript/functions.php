<?php

namespace PLUG\JavaScript;

use PLUG\JavaScript\JLex;
use PLUG\JavaScript\JTokenizer;

/**
 * Get name of Javascript terminal symbol
 * @param int scalar symbol
 * @return string
 */	
function j_token_name( $t ){
	$Lex = Lex::get('JLex');
	return $Lex->name( $t );
}

/**
 * Debugging function to print javascript token stream
 * @param array
 * @return void
 */	
function j_token_dump( array $tokens, $Lexclass = 'JLex' ){

	// instantiate Lex instance of appropriate type
	$Lex = Lex::get( $Lexclass );

	$line = 0;
	foreach( $tokens as $token ){
		
		list( $key, $value, $l ) = $token;

		if( $key === J_WHITESPACE || $key === J_LINE_TERMINATOR ){
			continue;
		}

		// print line number
		if( $l !== $line ){
			$line = $l;
			echo "#$line.\n";
		}
		
		// show token
		if( $key === $value || is_null($key) ){
			echo " \"", $value, "\" \n";
		}
		else {
			echo " ", $Lex->name($key), " : \"", $value, "\" \n";
		}
	}
}
 
 
/**
 * HTMLize JavaScript source for colourizing
 * @example JavaScript/j_token_html.php
 * @category PLUG
 * @param string
 * @param bool whether to keep whitespace and comments, default is true
 * @param bool whether to fully support Unicode
 * @param string optionally specify HTML tag instead of "OL", e.g. "DIV"
 * @return string
 */
function j_token_html( $src , $ws = true, $unicode = true, $ol = 'ol', $Lexclass = 'JLex' ){
	
	// instantiate Lex instance of appropriate type
	$Lex = Lex::get( $Lexclass );
		
	// convert non-unix line breaks
	// @todo  replace Unicode breaks too?
	$src = str_replace (
		array( "\r\n", "\r" ),
		array( "\n", "\n" ),
		$src
	);

	$tokens = j_token_get_all( $src, $ws, $unicode );
	
	switch( strtolower($ol) ){
	case 'ol':
	case 'ul':
		$li = 'li'; 
		break;
	default:
		$li = 'div';
	}
	
	$lines = array('');
	$line =& $lines[0];
	
	while( list(,$token) = each($tokens) ){
		
		list( $t, $s, $l, $c ) = $token;
		
		if( $s === 'true' || $s === 'false' || $s === 'null' ){
			$class = 'J_LITERAL';
		}
		else if( $Lex->is_word($s) ){
			$class = 'J_KEYWORD';
		}
		else if( ! is_int($t) && $s === $t ){
			$class = 'J_PUNCTUATOR';
		}
		else {
			$class = $Lex->name( $t );
		}
		while( isset($s{0}) ){
			if( ! preg_match('/^(.*)\n/', $s, $r ) ){
				$lines[0] .= '<span class="'.$class.'">'._j_htmlentities($s).'</span>';
				break;
			}
			$lines[0] .= '<span class="'.$class.'">'._j_htmlentities($r[1]).'</span>';
			array_unshift( $lines, '' );
			$s = substr( $s, strlen($r[0]) );
		}
	}
	$src = "</$ol>";
	foreach( $lines as $i => $s ){
		$class = $i & 1 ? 'odd' : 'even';
		$src = "<$li class=\"$class\">$s</$li>\n$src";
	}
	return "<$ol class=\"javascript\">\n$src";
}


/**
 * utility func
 * @ignore
 */
function _j_htmlentities( $s ){
	if( $s === '' ){
		return '&nbsp;';
	}
	$s = htmlentities( $s, ENT_COMPAT, 'utf-8');
	$s = str_replace( array(' ',"\t"), array('&nbsp;','&nbsp;&nbsp;&nbsp;'), $s );
	return $s;
}

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
?>