<?php
/**
 * Auto-generated file containing class BNFLex
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage bnf
 * @version $Id: BNFLex.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */

import('PLUG.parsing.Lex');

define( 'BNF_RULES', 1 );
define( 'BNF_RULE', 2 );
define( 'BNF_EMPTY_RULE', 3 );
define( 'BNF_EXPRESSION', 4 );
define( 'BNF_LIST', 5 );
define( 'BNF_TERM', 6 );
define( 'BNF_RULE_END', 7 );
define( 'BNF_TEXT', 8 );
define( 'BNF_LITERAL', 9 );

/**
 * Auto-generated Lex subclass
 * @category PLUG
 * @package parsing
 * @subpackage bnf
 */
class BNFLex extends Lex {

/** @var int */
protected $i = 9;

/** @var array */
protected $names = array (
  -3 => 'P_GOAL',
  -2 => 'P_EOF',
  -1 => 'P_EPSILON',
  1 => 'BNF_RULES',
  2 => 'BNF_RULE',
  3 => 'BNF_EMPTY_RULE',
  4 => 'BNF_EXPRESSION',
  5 => 'BNF_LIST',
  6 => 'BNF_TERM',
  7 => 'BNF_RULE_END',
  8 => 'BNF_TEXT',
  9 => 'BNF_LITERAL',
);
/** @var array */
protected $literals = array (
  ':' => 1,
  '<' => 1,
  '>' => 1,
  '|' => 1,
);

}
