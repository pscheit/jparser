<?php

namespace PLUG\functions\string;

/**
 * File containing function csv_split
 * @author Tim Whitlock
 * @category PLUG
 * @package functions
 * @subpackage string
 * @version $Id: csv_split.php,v 1.1 2009/03/22 12:44:44 twhitlock Exp $
 */

 
 
/**
 * Complex split operation of delimited list of optionally quoted values.
 * @example functions/string/csv_split.php See example usage
 * @param string Delimited and quoted string to split into array
 * @param string Optional value separator; defaults to ",".
 * @param bool Optional unescape flag; defaults to "\"
 * @return array split list of values
 */
function csv_split( $src, $comma = ',', $esc = '\\' ){
	$a = array();
	while( $src ){
		$c = $src{0};
		switch( $c ){
		// permit empty values
		case ',':
			$a[] = '';
			$src = substr( $src, 1 );
			continue 2;
		// ignore whitespace
		case ' ':
		case "\t":
			preg_match('/^\s+/', $src, $r );
			$src = substr( $src, strlen($r[0]) );
			continue 2;
		// quoted values
		case '"':
		case "'":
		case '`':
			$reg = sprintf('/^%1$s((?:\\\\.|[^%1$s\\\\])*)%1$s\s*(?:,|$)/', $c );
			break;
		// naked values
		default:
			$reg = '/^((?:\\\\.|[^,\\\\])*)(?:,|$)/';
			$c = ',';
		}
		if( preg_match( $reg, $src, $r ) ){
			$a[] = empty($r[1]) ? '' : str_replace( '\\'.$c, $c, $r[1] );
			$src = substr( $src, strlen($r[0]) );
			continue;
		}
		// else fail
		trigger_error("csv_split failure", E_USER_WARNING );
		break;
	}
	return $a;
}
