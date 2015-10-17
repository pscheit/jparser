<?php

namespace PLUG\parsing\LR;

/**
 * File containing class LRParseTableBuilder
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage LR
 * @version $Id: LRParseTableBuilder.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */


use PLUG\parsing\LR\LRParseTable;


/**
 * LR Parse Table Builder
 * @example parsing/LR/LRParseTableBuilder.php
 * @category PLUG
 * @package parsing
 * @subpackage LR
 */
class LRParseTableBuilder extends LRParseTable {

	
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
		"use PLUG\parsing\LR\LRParseTable;\n",
		"\n",
		"/**\n * Auto-generated LRParseTable subclass";
		// unset page level doc block tags we don't want against the class
		unset (
			$commentData['author'], $commentData['version']
		);
		foreach( $commentData as $tag => $value ){
			echo "\n * @$tag $value";
		}
		echo 
		"\n */\n",
		"class $classname extends LRParseTable {\n",
		"\n",
		"/** @var array */\n",
		"protected \$table = ",var_export($this->table,1),";\n\n",
		"}\n";
	}	
	

 
 
 
 }
 
 