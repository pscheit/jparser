<?php
/**
 * File containing class IncArgumentNode
 * @author Tim Whitlock
 * @category PLUG
 * @package compiler
 * @subpackage miniparsers
 * @version $Id: IncArgumentNode.php,v 1.1 2009/03/22 12:58:58 twhitlock Exp $
 */

import('PLUG.parsing.LR.LRParseNode');
import('PLUG.parsing.utils.decapse_string');

 
/**
 * Parse node for Include statement arguments.
 * Handles the evaluation of the whole string.
 * Only a very limited range of expressions are currently supported
 * @category PLUG
 * @package compiler
 * @subpackage miniparsers
 */
class IncArgumentNode extends LRParseNode {


	/**
	 * @override
	 */
	function compile_string( array $consts, array $vars ){
		$value = '';
		do{
			$Child = $this->current();
			// Significant nodes are strings, and concatenators
			// brackets will be ignored, and all assumed to have parsed successfully, so no point validating
			switch( $Child->scalar_symbol() ){
			case NT_STRING:
				$StrNode = $Child->current();
				$t = $StrNode->scalar_symbol();
				$s = $StrNode->__toString();
				
				switch( $t ){
				
				// generic string reference
				case T_STRING:
					// should be a constant
					if( isset($consts[$s]) ){
						// set from registry of constants
						$value .= (string) $consts[$s];
					}
					else if( defined($s) ){
						// else is a built-in constant
						trigger_error( "Constant not registered ($s), using current value", E_USER_NOTICE );
						$value .= (string) constant($s);
					}
					else {
						trigger_error("Spurious T_STRING ($s) in argument", E_USER_WARNING );
						$value .= $s;
					}
					continue 2;
				

				// string literal
				case T_CONSTANT_ENCAPSED_STRING:
					$value .= decapse_string( $s );
					continue 2;

					
				// scalar variable, can only be global. 
				// dodgy - best avoided
				case T_VARIABLE:
					$v = substr( $s, 1 );
					if( array_key_exists( $v, $vars ) ){
						$value .= (string) $vars[$v];
					}
					else if( array_key_exists( $v, $GLOBALS ) ){
						$value .= (string) $GLOBALS[$v];
					}
					else {
						trigger_error("Unknown variable $s", E_USER_WARNING );
					}
					continue 2;
				

				
				// special proccessing of array access vars
				case NT_ARRAY_ACCESS:
					$v = $StrNode->reset()->__toString();
					$v = substr( $v, 1 );
					if( isset($vars[$v]) && is_array($vars[$v]) ){
						$a = $vars[$v];
					}
					else if( isset($GLOBALS[$v]) && is_array($GLOBALS[$v]) ){
						trigger_error( "Variable not registered (\$$v), using global", E_USER_NOTICE );
						$a = $GLOBALS[$v];
					}
					else {
						trigger_error("Unknown variable $v", E_USER_WARNING );
						continue 2;
					}					
					$StrNode->next();
					$StrNode = $StrNode->next();
					$k = $StrNode->__toString();
					if( is_numeric($k) ){
						$k = (int) $k;
					}
					else {
						$k = decapse_string( $k );
					}
					if( array_key_exists( $k, $a ) ){
						$value .= (string) $a[$k];
					}
					else {
						trigger_error("Spurious array access (no index $k in $v)", E_USER_WARNING );
					}
					continue 2;
				
				
				// sandboxed function calls
				case NT_FUNC_CALL:
					$funcname = $StrNode->current()->__toString();
					switch( $funcname ){
					case 'dirname':
					case 'basename':
						break;
					default:
						trigger_error("function '$funcname' not supported, sorry", E_USER_WARNING );
						continue 2;
					}
					if( ! function_exists($funcname) ){
						trigger_error("Spurious function '$funcname'", E_USER_WARNING );
						continue 2;
					}
					// collect function arguments
					$args = array();
					$argnodes = $StrNode->get_nodes_by_symbol( NT_ARG, 2 );
					foreach( $argnodes as $i => $arg ){
						$args[] = $arg->compile_string( $consts, $vars );
					}
					// ok to call function
					$value .= (string) call_user_func_array( $funcname, $args );
					continue 2;
				
				
				// special constants need to be passing in via the $consts argument
				case T_FILE:
					if( ! isset($consts[$s]) ){
						trigger_error( "$s not passed as a registered constant", E_USER_WARNING );
					}
					else {
						$value .= $consts[$s];
					}
					continue 2;

				
				// else warn that this is unsupported
				default:
					trigger_error("Unexpected node ($t) with value `$StrNode'", E_USER_WARNING );
					continue 2;
					
					
				}
				// end strNode switch
			}
			// end child switch
		}
		while( $this->next() );
		return $value;
	}




}
	
 
