<?php
/**
 * Auto-generated file containing class BNFGrammar
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage bnf
 * @version $Id: BNFGrammar.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */

import('PLUG.parsing.Grammar');

/**
 * Auto-generated Grammar subclass
 * @category PLUG
 * @package parsing
 * @subpackage bnf
 */
class BNFGrammar extends Grammar {

/** @override $goal */
protected $goal = 1;

/** @override $i */
protected $i = 26;

/** @override $rules */
protected $rules = array (
  -2 => 
  array (
    0 => -3,
    1 => 
    array (
      0 => 1,
      1 => -2,
    ),
  ),
  0 => 
  array (
    0 => 1,
    1 => 
    array (
      0 => 1,
      1 => 2,
    ),
  ),
  2 => 
  array (
    0 => 1,
    1 => 
    array (
      0 => 2,
    ),
  ),
  4 => 
  array (
    0 => 1,
    1 => 
    array (
      0 => 1,
      1 => 3,
    ),
  ),
  6 => 
  array (
    0 => 1,
    1 => 
    array (
      0 => 3,
    ),
  ),
  8 => 
  array (
    0 => 2,
    1 => 
    array (
      0 => '<',
      1 => 8,
      2 => '>',
      3 => ':',
      4 => 4,
      5 => 7,
    ),
  ),
  10 => 
  array (
    0 => 3,
    1 => 
    array (
      0 => 7,
    ),
  ),
  12 => 
  array (
    0 => 4,
    1 => 
    array (
      0 => 5,
    ),
  ),
  14 => 
  array (
    0 => 4,
    1 => 
    array (
      0 => 4,
      1 => '|',
      2 => 5,
    ),
  ),
  16 => 
  array (
    0 => 5,
    1 => 
    array (
      0 => 6,
    ),
  ),
  18 => 
  array (
    0 => 5,
    1 => 
    array (
      0 => 5,
      1 => 6,
    ),
  ),
  20 => 
  array (
    0 => 6,
    1 => 
    array (
      0 => 8,
    ),
  ),
  22 => 
  array (
    0 => 6,
    1 => 
    array (
      0 => 9,
    ),
  ),
  24 => 
  array (
    0 => 6,
    1 => 
    array (
      0 => '<',
      1 => 8,
      2 => '>',
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
  '<' => '<',
  8 => 8,
  '>' => '>',
  ':' => ':',
  7 => 7,
  '|' => '|',
  9 => 9,
);

/** @override $nts */
protected $nts = array (
  1 => 1,
  2 => 2,
  3 => 3,
  4 => 4,
  5 => 5,
  6 => 6,
);

/** @override $firsts */
protected $firsts = array (
  2 => 
  array (
    '<' => '<',
  ),
  3 => 
  array (
    7 => 7,
  ),
  6 => 
  array (
    8 => 8,
    9 => 9,
    '<' => '<',
  ),
  1 => 
  array (
    '<' => '<',
    7 => 7,
  ),
  5 => 
  array (
    8 => 8,
    9 => 9,
    '<' => '<',
  ),
  -3 => 
  array (
    '<' => '<',
    7 => 7,
  ),
  4 => 
  array (
    8 => 8,
    9 => 9,
    '<' => '<',
  ),
);

/** @override $follows */
protected $follows = array (
  -3 => 
  array (
  ),
  -2 => 
  array (
  ),
  1 => 
  array (
    -2 => -2,
    '<' => '<',
    7 => 7,
  ),
  2 => 
  array (
    -2 => -2,
    '<' => '<',
    7 => 7,
  ),
  3 => 
  array (
    -2 => -2,
    '<' => '<',
    7 => 7,
  ),
  '<' => 
  array (
    8 => 8,
  ),
  8 => 
  array (
    '>' => '>',
    7 => 7,
    '|' => '|',
    8 => 8,
    9 => 9,
    '<' => '<',
  ),
  '>' => 
  array (
    ':' => ':',
    7 => 7,
    '|' => '|',
    8 => 8,
    9 => 9,
    '<' => '<',
  ),
  ':' => 
  array (
    8 => 8,
    9 => 9,
    '<' => '<',
  ),
  4 => 
  array (
    7 => 7,
    '|' => '|',
  ),
  7 => 
  array (
    -2 => -2,
    '<' => '<',
    7 => 7,
  ),
  5 => 
  array (
    7 => 7,
    '|' => '|',
    8 => 8,
    9 => 9,
    '<' => '<',
  ),
  '|' => 
  array (
    8 => 8,
    9 => 9,
    '<' => '<',
  ),
  6 => 
  array (
    7 => 7,
    '|' => '|',
    8 => 8,
    9 => 9,
    '<' => '<',
  ),
  9 => 
  array (
    7 => 7,
    '|' => '|',
    8 => 8,
    9 => 9,
    '<' => '<',
  ),
);

/** @override $ntindex */
protected $ntindex = array (
  -3 => 
  array (
    0 => -2,
  ),
  1 => 
  array (
    0 => 0,
    1 => 2,
    2 => 4,
    3 => 6,
  ),
  2 => 
  array (
    0 => 8,
  ),
  3 => 
  array (
    0 => 10,
  ),
  4 => 
  array (
    0 => 12,
    1 => 14,
  ),
  5 => 
  array (
    0 => 16,
    1 => 18,
  ),
  6 => 
  array (
    0 => 20,
    1 => 22,
    2 => 24,
  ),
);

}
