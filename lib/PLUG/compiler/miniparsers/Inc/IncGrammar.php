<?php
/**
 * Auto-generated file containing class IncGrammar
 */

import('PLUG.parsing.Grammar');

/**
 * Auto-generated Grammar subclass
 */
class IncGrammar extends Grammar {

/** @override $goal */
protected $goal = 501;

/** @override $i */
protected $i = 40;

/** @override $rules */
protected $rules = array (
  -2 => 
  array (
    0 => -3,
    1 => 
    array (
      0 => 501,
      1 => -2,
    ),
  ),
  0 => 
  array (
    0 => 501,
    1 => 
    array (
      0 => 502,
      1 => ';',
    ),
  ),
  2 => 
  array (
    0 => 501,
    1 => 
    array (
      0 => 503,
      1 => ';',
    ),
  ),
  4 => 
  array (
    0 => 502,
    1 => 
    array (
      0 => 504,
      1 => 505,
    ),
  ),
  6 => 
  array (
    0 => 503,
    1 => 
    array (
      0 => 307,
      1 => '(',
      2 => 505,
      3 => ')',
    ),
  ),
  8 => 
  array (
    0 => 504,
    1 => 
    array (
      0 => 262,
    ),
  ),
  10 => 
  array (
    0 => 504,
    1 => 
    array (
      0 => 261,
    ),
  ),
  12 => 
  array (
    0 => 504,
    1 => 
    array (
      0 => 259,
    ),
  ),
  14 => 
  array (
    0 => 504,
    1 => 
    array (
      0 => 258,
    ),
  ),
  16 => 
  array (
    0 => 505,
    1 => 
    array (
      0 => 506,
    ),
  ),
  18 => 
  array (
    0 => 505,
    1 => 
    array (
      0 => 506,
      1 => ',',
      2 => 505,
    ),
  ),
  20 => 
  array (
    0 => 506,
    1 => 
    array (
      0 => 507,
    ),
  ),
  22 => 
  array (
    0 => 506,
    1 => 
    array (
      0 => 507,
      1 => '.',
      2 => 506,
    ),
  ),
  24 => 
  array (
    0 => 507,
    1 => 
    array (
      0 => 315,
    ),
  ),
  26 => 
  array (
    0 => 507,
    1 => 
    array (
      0 => 307,
    ),
  ),
  28 => 
  array (
    0 => 507,
    1 => 
    array (
      0 => 364,
    ),
  ),
  30 => 
  array (
    0 => 507,
    1 => 
    array (
      0 => 309,
    ),
  ),
  32 => 
  array (
    0 => 507,
    1 => 
    array (
      0 => 503,
    ),
  ),
  34 => 
  array (
    0 => 507,
    1 => 
    array (
      0 => 508,
    ),
  ),
  36 => 
  array (
    0 => 508,
    1 => 
    array (
      0 => 309,
      1 => '[',
      2 => 305,
      3 => ']',
    ),
  ),
  38 => 
  array (
    0 => 508,
    1 => 
    array (
      0 => 309,
      1 => '[',
      2 => 315,
      3 => ']',
    ),
  ),
);

/** @override $excludela */
protected $excludela = array (
);

/** @override $ts */
protected $ts = array (
  -1 => -1,
  -2 => -2,
  ';' => ';',
  307 => 307,
  '(' => '(',
  ')' => ')',
  262 => 262,
  261 => 261,
  259 => 259,
  258 => 258,
  ',' => ',',
  '.' => '.',
  315 => 315,
  364 => 364,
  309 => 309,
  '[' => '[',
  305 => 305,
  ']' => ']',
);

/** @override $nts */
protected $nts = array (
  501 => 501,
  502 => 502,
  503 => 503,
  504 => 504,
  505 => 505,
  506 => 506,
  507 => 507,
  508 => 508,
);

/** @override $ntindex */
protected $ntindex = array (
  -3 => 
  array (
    0 => -2,
  ),
  501 => 
  array (
    0 => 0,
    1 => 2,
  ),
  502 => 
  array (
    0 => 4,
  ),
  503 => 
  array (
    0 => 6,
  ),
  504 => 
  array (
    0 => 8,
    1 => 10,
    2 => 12,
    3 => 14,
  ),
  505 => 
  array (
    0 => 16,
    1 => 18,
  ),
  506 => 
  array (
    0 => 20,
    1 => 22,
  ),
  507 => 
  array (
    0 => 24,
    1 => 26,
    2 => 28,
    3 => 30,
    4 => 32,
    5 => 34,
  ),
  508 => 
  array (
    0 => 36,
    1 => 38,
  ),
);

}
