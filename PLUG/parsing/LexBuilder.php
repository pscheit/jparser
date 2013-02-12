<?php
/**
 * File containing class LexBuilder
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @version $Id: LexBuilder.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */
 

import('PLUG.parsing.Lex');


/**
 * Exportable Lex class
 * @example parsing/LexBuilder.php
 * @category PLUG
 * @package parsing
 */
class LexBuilder extends Lex {

	
	/**
	 * Remember which symbols were re-defined to avoid duplicate when exporting
	 * @var array
	 */
	private $redefinitions = array (
		P_EPSILON => true,
		P_EOF     => true,
		P_GOAL    => true,
	);

	

	/**
	 * Define a symbol in the lexicon, e.g. "J_NUMBER"
	 * @param string internal name which will be defined as a php constant
	 * @param string optional alternative human-readable name 
	 * @return int value of defined constant
	 */	
	function define( $c, $alt = '' ){
		if( ! is_string($c) ){
			trigger_error("symbol $c must be a string", E_USER_WARNING );
			return null;
		}
		if( defined($c) ){
			trigger_error("Conflict on symbol $c, already defined", E_USER_ERROR );
		}
		define( $c, ++ $this->i );
		$this->names[ $this->i ] = $alt ? $alt : $c;
		return $this->i;
	}
		
	
	
	/**
	 * Define a literal string symbol in the lexicon, e.g. ";"
	 * @param string literal symbol
	 * @return int length of symbol
	 */	
	function define_literal( $s ){
		return $this->literals[$s] = strlen($s);
	}
		
	
	
	/**
	 * Redefine an existing symbol, e.g. a PHP Tokenizer constant such as "T_STRING"
	 * Ensure to make all redefinitions before any new definitions to avoid conflicts
	 * @param string
	 * @return int
	 */	
	function redefine( $c ){
		$i = constant( $c );
		if( isset( $this->names[$i] ) ){
			$name = $this->names[$i];
			trigger_error("Conflict on symbol $c, $i already in use for $name", E_USER_ERROR );
		}
		$this->redefinitions[$i] = true;
		$this->names[$i] = $c;
		return $this->i = max( $i, $this->i );
	}
	
	
	
	/**
	 * print out a subclass definition with this object's members embedded
	 * @param string name of extended class
	 * @param array optional doc block meta data, e.g. [ 'package' => 'parsing'  ]
	 * @return void
	 */	
	function class_export( $classname, array $commentData = array() ){
		echo 
		"/**\n * Auto-generated file containing class $classname";
		foreach( $commentData as $tag => $value ){
			echo "\n * @$tag $value";
		}
		echo 
		"\n */\n",
		"\n",
		"import('PLUG.parsing.Lex');\n",
		"\n";
		// hard-define integer symbols
		// excluding re-definitions or existing symbols
		ksort( $this->names );
		foreach( $this->names as $i => $c ){
			if( ! is_int($i) ){
				continue;
			}
			if( isset($this->redefinitions[$i]) ){
				//echo "// redefinition: $c = $i \n";
			}
			else {
				echo "define( ",var_export($c,1),", $i );\n";
			}
		}
		// define subclass with overloaded members
		$i = var_export( $this->i, 1 );
		echo 
		"\n",
		"/**\n * Auto-generated Lex subclass";
		// unset page level doc block tags we don't want against the class
		unset (
			$commentData['author'], $commentData['version']
		);
		foreach( $commentData as $tag => $value ){
			echo "\n * @$tag $value";
		}
		echo 
		"\n */\n",
		"class $classname extends Lex {\n",
		"\n",
		// overload $i
		"/** @var int */\n",
		"protected \$i = $i;\n",
		"\n",
		// overload names, parent class's $names array will not need to be consulted
		"/** @var array */\n",
		"protected \$names = ",var_export( $this->names, 1 ),";\n",
		// overload literals, parent class's $literals array will not need to be consulted
		"/** @var array */\n",
		"protected \$literals = ",var_export( $this->literals, 1 ),";\n",
		"\n",
		// end class
		"}\n";
	}	

}

