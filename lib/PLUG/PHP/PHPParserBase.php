<?php
/**
 * File containing class PHPParserBase
 * @author Tim Whitlock
 * @category PLUG
 * @package PHP
 * @version $Id: PHPParserBase.php,v 1.1 2009/03/22 12:56:05 twhitlock Exp $
 */

 
import('PLUG.parsing.LR.LRParser'); 

 
/**
 * Abstract base class to be extended by any parser implementation that uses the PHP tokenizer
 * @category PLUG
 * @package PHP
 */
abstract class PHPParserBase extends LRParser {

	
	
	
	/**
	 * @override
	 */	
	protected function current_token(){
		$t = parent::current_token();
		if (
			is_array( $t ) && (
				$t[0] === T_WHITESPACE || 
				$t[0] === T_COMMENT || 
				$t[0] === T_DOC_COMMENT
			)
		){
			$t = $this->next_token();
		}
		return $t;
	}
	
	
	
	/**
	 * @override
	 */
	protected function next_token(){
		do {
			$t = parent::next_token();
		} 
		while ( 
			is_array( $t ) && (
				$t[0] === T_WHITESPACE || 
				$t[0] === T_COMMENT || 
				$t[0] === T_DOC_COMMENT
			)
		);
		return $t;
	}
	
	
	
	
	/**
	 * @override
	 */
	protected function prev_token(){
		do {
			$t = parent::prev_token();
		} 
		while ( 
			is_array( $t ) && (
				$t[0] === T_WHITESPACE || 
				$t[0] === T_COMMENT || 
				$t[0] === T_DOC_COMMENT
			)
		);
		return $t;
	}
	
	
	
}
	
 
 
