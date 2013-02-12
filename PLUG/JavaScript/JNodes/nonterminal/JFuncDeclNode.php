<?php
/**
 * File containing class JFuncDeclNode
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JFuncDeclNode.php,v 1.1 2009/03/22 12:51:08 twhitlock Exp $
 */
 
 
/**
 * JavaScript node class for the J_FUNC_DECL symbol
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JFuncDeclNode extends JNodeBase {


	
	
	/**
	 * Mainly to avoid recursion in defaul JNodeBase::obfuscate
	 */
	function obfuscate( array &$names ){
		$this->reset();
		$Identifier = $this->next();
		// obfuscate identifier if there is one
		if( $Identifier->is_symbol(J_IDENTIFIER) ){
			$Identifier->__obfuscate( $names ); 
			// hop over opening bracket
			$this->next();
		}
		unset( $Identifier );
		// move to param list
		$Params = $this->next();
		if( $Params->is_symbol(J_PARAM_LIST) ){
			$Params->obfuscate( $names );
			// hop over closing bracket
			$this->next();
		}
		unset( $Params );
		// hop over opening brace
		$this->next();
		// move to function body
		$Body = $this->next();
		$Body->obfuscate( $names );
	}
	
	
	
	/**
	 * @override
	 */
	function format_lines( &$line, array &$lines ){
		$Node = $this->reset();
		do {
			$Node->format_lines( $line, $lines );
		}
		while( 
			( $Node = $this->next() ) && ! $Node->is_symbol(J_FUNC_BODY)
		);
		// at body, break after "{"
		JNodeBase::format_newline('', $line, $lines );
		$funclines = array();
		$Node->format_lines( $line, $funclines );
		// indent function lines
		foreach( $funclines as $funcline ){
			$lines[] = "\t".$funcline;
		}
		// close body
		// only break after declarations
		if( $this->is_symbol(J_FUNC_EXPR) ){
			$line .= '} ';
		}
		else {
			if( $line ){
				JNodeBase::format_newline('', $line, $lines );
			}
			JNodeBase::format_newline('}', $line, $lines );
		}
	
	}

}
