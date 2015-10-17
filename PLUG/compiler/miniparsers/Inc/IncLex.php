<?php

namespace PLUG\compiler\miniparsers\Inc;

/**
 * Auto-generated file containing class IncLex
 */

use PLUG\parsing\Lex;

define( 'NT_STATEMENT', 501 );
define( 'NT_CONSTRUCT', 502 );
define( 'NT_FUNC_CALL', 503 );
define( 'NT_KEYWORD', 504 );
define( 'NT_ARG_LIST', 505 );
define( 'NT_ARG', 506 );
define( 'NT_STRING', 507 );
define( 'NT_ARRAY_ACCESS', 508 );

/**
 * Auto-generated Lex subclass
 */
class IncLex extends Lex {

/** @var int */
protected $i = 508;

/** @var array */
protected $names = array (
  -3 => 'P_GOAL',
  -2 => 'P_EOF',
  -1 => 'P_EPSILON',
  258 => 'T_REQUIRE_ONCE',
  259 => 'T_REQUIRE',
  261 => 'T_INCLUDE_ONCE',
  262 => 'T_INCLUDE',
  305 => 'T_LNUMBER',
  307 => 'T_STRING',
  309 => 'T_VARIABLE',
  315 => 'T_CONSTANT_ENCAPSED_STRING',
  364 => 'T_FILE',
  501 => 'NT_STATEMENT',
  502 => 'NT_CONSTRUCT',
  503 => 'NT_FUNC_CALL',
  504 => 'NT_KEYWORD',
  505 => 'NT_ARG_LIST',
  506 => 'NT_ARG',
  507 => 'NT_STRING',
  508 => 'NT_ARRAY_ACCESS',
);
/** @var array */
protected $literals = array (
  ';' => 1,
  '(' => 1,
  ')' => 1,
  ',' => 1,
  '.' => 1,
  '[' => 1,
  ']' => 1,
);

}
