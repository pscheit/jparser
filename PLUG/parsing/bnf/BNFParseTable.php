<?php

namespace PLUG\parsing\bnf;

/**
 * Auto-generated file containing class BNFParseTable
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage bnf
 * @version $Id: BNFParseTable.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */

use PLUG\parsing\LR\LRParseTable;

/**
 * Auto-generated LRParseTable subclass
 * @category PLUG
 * @package parsing
 * @subpackage bnf
 */
class BNFParseTable extends LRParseTable {

/** @var array */
protected $table = array (
  1 => 
  array (
    1 => 3,
    2 => 45,
    '<' => 11,
    3 => 47,
    7 => 43,
  ),
  3 => 
  array (
    2 => 5,
    3 => 7,
    -2 => 9,
    '<' => 11,
    7 => 43,
  ),
  5 => 
  array (
    7 => 0,
    '<' => 0,
    -2 => 0,
  ),
  7 => 
  array (
    7 => 4,
    '<' => 4,
    -2 => 4,
  ),
  9 => 
  array (
    -3 => -2,
  ),
  11 => 
  array (
    8 => 13,
  ),
  13 => 
  array (
    '>' => 15,
  ),
  15 => 
  array (
    ':' => 17,
  ),
  17 => 
  array (
    4 => 19,
    5 => 41,
    6 => 37,
    8 => 27,
    9 => 29,
    '<' => 31,
  ),
  19 => 
  array (
    '|' => 21,
    7 => 39,
  ),
  21 => 
  array (
    5 => 23,
    6 => 37,
    8 => 27,
    9 => 29,
    '<' => 31,
  ),
  23 => 
  array (
    '|' => 14,
    7 => 14,
    6 => 25,
    8 => 27,
    9 => 29,
    '<' => 31,
  ),
  25 => 
  array (
    '<' => 18,
    9 => 18,
    8 => 18,
    '|' => 18,
    7 => 18,
  ),
  27 => 
  array (
    7 => 20,
    '|' => 20,
    8 => 20,
    9 => 20,
    '<' => 20,
  ),
  29 => 
  array (
    7 => 22,
    '|' => 22,
    8 => 22,
    9 => 22,
    '<' => 22,
  ),
  31 => 
  array (
    8 => 33,
  ),
  33 => 
  array (
    '>' => 35,
  ),
  35 => 
  array (
    7 => 24,
    '|' => 24,
    8 => 24,
    9 => 24,
    '<' => 24,
  ),
  37 => 
  array (
    7 => 16,
    '|' => 16,
    8 => 16,
    9 => 16,
    '<' => 16,
  ),
  39 => 
  array (
    7 => 8,
    '<' => 8,
    -2 => 8,
  ),
  41 => 
  array (
    7 => 12,
    '|' => 12,
    6 => 25,
    8 => 27,
    9 => 29,
    '<' => 31,
  ),
  43 => 
  array (
    7 => 10,
    '<' => 10,
    -2 => 10,
  ),
  45 => 
  array (
    7 => 2,
    '<' => 2,
    -2 => 2,
  ),
  47 => 
  array (
    7 => 6,
    '<' => 6,
    -2 => 6,
  ),
);

}
