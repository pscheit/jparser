#!/usr/bin/php
<?php

namespace PLUG\parsing\bnf\dev;

require dirname(__FILE__).'/../../../plugcli.php';
use PLUG\parsing\LexBuilder;
use PLUG\parsing\GrammarBuilder;
use PLUG\parsing\LR\LRNDA;


// Manually construct the BNF Lexicon
//
$Lex = new LexBuilder;
// non terminals 
$Lex->define('BNF_RULES'); 
$Lex->define('BNF_RULE'); 
$Lex->define('BNF_EMPTY_RULE'); 
$Lex->define('BNF_EXPRESSION'); 
$Lex->define('BNF_LIST'); 
$Lex->define('BNF_TERM'); 
// terminals
$Lex->define('BNF_RULE_END'); 
$Lex->define('BNF_TEXT'); 
$Lex->define('BNF_LITERAL'); 
$Lex->define_literal(':'); 
$Lex->define_literal('<'); 
$Lex->define_literal('>'); 
$Lex->define_literal('|'); 



// Manually construct the BNF Grammar in its raw form
//
$raw = array (
	BNF_RULES => array (
		array( BNF_RULES, BNF_RULE ),
		array( BNF_RULE ),
		array( BNF_RULES, BNF_EMPTY_RULE ),
		array( BNF_EMPTY_RULE ),
	),
	BNF_RULE => array (
		array( '<', BNF_TEXT, '>', ':', BNF_EXPRESSION, BNF_RULE_END ),
	),
	BNF_EMPTY_RULE => array(
		array( BNF_RULE_END )
	),
	BNF_EXPRESSION => array (
		array( BNF_LIST ),
		array( BNF_EXPRESSION, '|', BNF_LIST ),
	),
	BNF_LIST => array (
		array( BNF_TERM ),
		array( BNF_LIST, BNF_TERM ),
	),
	BNF_TERM => array (
		array( BNF_TEXT ),
		array( BNF_LITERAL ),
		array( '<', BNF_TEXT, '>'),
	),
);
$Grammar = GrammarBuilder::make( $raw );


// Construct LR(1) Parse Table
//
$NDA = new LRNDA( $Grammar, 1 );
$Table = $NDA->export();




// Output classes
//
$commentData = array(
	'author'     => 'Tim Whitlock',
	'category'   => 'PLUG',
	'package'    => 'parsing',
	'subpackage' => 'bnf',
	'version'    => '$Id'.'$',
);
@ob_end_flush();
ob_start();
$Lex->class_export('BNFLex', $commentData );
file_put_contents( '../BNFLex.php', "<?php\n".ob_get_contents() );
ob_clean();

$Grammar->class_export('BNFGrammar', $commentData );
file_put_contents( '../BNFGrammar.php', "<?php\n".ob_get_contents() );
ob_clean();

$Table->class_export('BNFParseTable', $commentData );
file_put_contents( '../BNFParseTable.php', "<?php\n".ob_get_contents() );
ob_end_clean();








