<?php

namespace PLUG\JavaScript;

/**
 * File containing class JLex
 * @category PLUG
 * @package JavaScript
 */


/**
 * Require auto-generated super class containing symbol definitions
 */
use PLUG\JavaScript\JLexBase;
use PLUG\parsing\Lex;


/**
 * Javascript/ECMAScript Lex object.
 * @example JavaScript/JLex.php
 * @category PLUG
 * @package JavaScript
 */
class JLex extends JLexBase {
	
	/**
	 * @var array
	 */
	protected $words = array (
		// Literals
		'true'   => J_TRUE,
		'false'  => J_FALSE,
		'null'   => J_NULL,
		// Keyword symbols
		'break'    => J_BREAK,     'else'       => J_ELSE,        'new'    => J_NEW,     'var'   => J_VAR,  
		'case'     => J_CASE,      'finally'    => J_FINALLY,     'return' => J_RETURN,  'void'  => J_VOID,  
		'catch'    => J_CATCH,     'for'        => J_FOR,         'switch' => J_SWITCH,  'while' => J_WHILE,  
		'continue' => J_CONTINUE,  'function'   => J_FUNCTION,    'this'   => J_THIS,    'with'  => J_WITH,  
		'default'  => J_DEFAULT,   'if'         => J_IF,          'throw'  => J_THROW,  
		'delete'   => J_DELETE,    'in'         => J_IN,          'try'    => J_TRY,  
		'do'       => J_DO,        'instanceof' => J_INSTANCEOF,  'typeof' => J_TYPEOF,
	 	// Reserved symbols
		'abstract' => J_ABSTRACT,  'enum'       => J_ENUM,       'int'       => J_INT,        'short'        => J_SHORT,  
		'boolean'  => J_BOOLEAN,   'export'     => J_EXPORT,     'interface' => J_INTERFACE,  'static'       => J_STATIC,  
		'byte'     => J_BYTE,      'extends'    => J_EXTENDS,    'long'      => J_LONG,       'super'        => J_SUPER,  
		'char'     => J_CHAR,      'final'      => J_FINAL,      'native'    => J_NATIVE,     'synchronized' => J_SYNCHRONIZED,  
		'class'    => J_CLASS,     'float'      => J_FLOAT,      'package'   => J_PACKAGE,    'throws'       => J_THROWS,  
		'const'    => J_CONST,     'goto'       => J_GOTO,       'private'   => J_PRIVATE,    'transient'    => J_TRANSIENT,  
		'debugger' => J_DEBUGGER,  'implements' => J_IMPLEMENTS, 'protected' => J_PROTECTED,  'volatile'     => J_VOLATILE,  
		'double'   => J_DOUBLE,    'import'     => J_IMPORT,     'public'    => J_PUBLIC, 
	);


	/**
	 * @param string
	 * @return bool
	 */
	function is_word( $s ){
		return isset($this->words[$s]) ? $this->words[$s] : false;
	}


	/**
	 * Short cut to Lex::get('JLex') useful for code-hinting
	 * @return JLex
	 */
	static function singleton(){
		return Lex::get(__CLASS__);
	}
	

}
