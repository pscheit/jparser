<?php
/**
 * File containing function php_highlight_string
 * @author Tim Whitlock
 * @category PLUG
 * @package functions
 * @subpackage PHP
 * @version $Id: php_highlight_string.php,v 1.1 2009/03/22 12:44:44 twhitlock Exp $
 */

 
 
/**
 * Replacement for {@link highlight_string} using class names instead of font colours
 * @example functions/PHP/php_highlight_string.php
 * @param string source code
 * @return string html source
 */
function php_highlight_string( $src ){
	// convert non-unix line breaks
	$src = str_replace (
		array( "\r\n", "\r" ),
		array( "\n", "\n" ),
		$src
	);
	
	$tokens = token_get_all( $src );
	$lines = array('');
	$quot = '';
	foreach( $tokens as $token ){
		if( is_array($token) ){
			list($t,$s) = $token;
			$class = token_name($t);
			// categorize token into broader class
			switch( $t ){
			case T_ECHO:
			case T_SWITCH:
			case T_BREAK:
			case T_RETURN:
			case T_FOR:
			case T_FOREACH:
			case T_REQUIRE:
			case T_REQUIRE_ONCE:
			case T_INCLUDE:
			case T_INCLUDE_ONCE:
			case T_CLASS:
			case T_PUBLIC:
			case T_PRIVATE:
			case T_PROTECTED:
			case T_FUNCTION:
			case T_STATIC:
			case T_FINAL:
			case T_ABSTRACT:
			case T_IF:
			case T_ELSE:
			case T_ISSET:
			case T_UNSET:
			case T_EMPTY:
				$class .= ' PHP_KEYWORD';
				break;
			}
			if( $quot || $t === T_CONSTANT_ENCAPSED_STRING ){
				$class .= ' PHP_QUOTED';
			}
			while( $s !== '' ){
				if( ! preg_match('/^(.*)\n/', $s, $r ) ){
					$lines[0] .= '<span class="'.$class.'">'._php_highlight_string_escape($s).'</span>';
					break;
				}
				$lines[0] .= '<span class="'.$class.'">'._php_highlight_string_escape($r[1]).'</span>';
				array_unshift( $lines, '' );
				$s = substr( $s, strlen($r[0]) );
			}
		}
		else {
			if( $quot ){
				$class = 'PHP_QUOTED';
			}
			else {
				$class = '';
			}
			if( $quot && $token === $quot ){
				// end quote
				$quot = '';
			}
			else if( $token == "'" || $token === '"' ){
				// start quote
				$quot = $token;
				$class = 'PHP_QUOTED';
			}
			if( $class ){
				$lines[0] .= '<span class="'.$class.'">'._php_highlight_string_escape($token).'</span>';
			}
			else {
				$lines[0] .= _php_highlight_string_escape($token);
			}
		}
	}
	$src = '</ol>';
	foreach( $lines as $i => $s ){
		$class = $i & 1 ? 'odd' : 'even';
		$src = "<li class=\"$class\">$s</li>\n$src";
	}
	return "<ol class=\"php\">\n$src";
}	



/**
 * @internal
 * Escape source code to HTML
 */
function _php_highlight_string_escape( $s ){
	if( $s === '' ){
		return '&nbsp;';
	} 
	return str_replace ( 
		array(' ', "\t" ), 
		array('&nbsp;','&nbsp;&nbsp;&nbsp;&nbsp;'), 
		htmlspecialchars( $s, ENT_COMPAT, 'ISO-8859-1' ) 
	);
}
