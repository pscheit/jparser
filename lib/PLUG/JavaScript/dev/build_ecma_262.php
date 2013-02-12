#!/usr/bin/php
<?php
/**
 * Build JParser
 */
require dirname($argv[0]).'/../../plugcli.php';

import('PLUG.time.Timer');
import('PLUG.parsing.*');
import('PLUG.parsing.LR.*');
import('PLUG.parsing.bnf.*');


$lexname = 'JLexBase';
$grammarname = 'JGrammar';
$tablename = 'JParseTable';

$commentData = array(
	'author' => 'Tim Whitlock',
	'category' => 'PLUG',
	'package'  => 'JavaScript',
	'version'  => '$Id'.'$',
);




$Timer = new Timer();


$classdir = PLUG_HOST_DIR.'/PLUG/JavaScript';
$bnfpath = $classdir.'/dev/ecma_262.bnf';
$Tree = BNFParser::parse_file( $bnfpath );


// Make Lex object
$Lex = $Tree->make_lex();
$Lex->dump();
echo "----------\n";

// Make Grammar object
$rawgrammar = $Tree->evaluate();
$Grammar = GrammarBuilder::make( $rawgrammar );
// Load special rules
$Grammar->exclude_terminal( J_EXPR_STATEMENT, '{' );
$Grammar->exclude_terminal( J_EXPR_STATEMENT, J_FUNCTION );

// trash dummy rules that we used just to get redundant terminals into the Lex
$Grammar->remove_rules( J_RESERVED );
$Grammar->remove_rules( J_IGNORE );

$Grammar->dump( $Lex );
echo "----------\n";

printf( "Ready to build automaton after %f milliseconds\n", $Timer->reset() );

// Make Parse Table object
$NDA = new LRNDA( $Grammar, 1 );
printf( "NDA built in %f milliseconds\n", $Timer->reset() );

$Table = $NDA->export();
printf( "Table exported in %f milliseconds\n", $Timer->reset() );

/**
 * Dump classes into files
 */
@ob_end_flush();
ob_start();
$Lex->class_export( $lexname, $commentData );
file_put_contents( $classdir.'/'.$lexname.'.php', "<?php\n".ob_get_contents() );
ob_clean();
$Grammar->class_export( $grammarname, $commentData );
file_put_contents( $classdir.'/'.$grammarname.'.php', "<?php\n".ob_get_contents() );
ob_clean();
$Table->class_export( $tablename, $commentData );
file_put_contents( $classdir.'/'.$tablename.'.php', "<?php\n".ob_get_contents() );
ob_end_clean();




echo '
Done
';

