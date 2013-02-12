<?php

namespace PLUG\parsing;
/**
 * File containing function PLUG.parsing.utils.decapse_string
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage utils
 * @version $Id: decapse_string.php,v 1.1 2009/03/22 12:55:31 twhitlock Exp $
 */

class Util {
 
  /**
   * Decapse an encapsed string, as per T_CONSTANT_ENCAPSED_STRING.
   * - Uses str_replace instead of stripslashes, so slashes are only removed from target quotes.
   * - Does not validate that string is correctly terminated
   * @example parsing/utils/decapse_string.php
   * @param string encapsed string, e.g. <code>'Tim\'s shoes are \'awesome\'!'</code>
   * @return string native decapsed string, e.g. <code>Tim's shoes are 'awesome'!</code>
   */
  function decapse_string( $s ){
    if( empty($s) ){
      return '';
    }
    $q = $s{0};
    switch( $q ){
    case "'":
    case '"':
    case '`':
      $s = substr( $s, 1, -1 );
      return str_replace( "\\$q", $q, $s );
    default:
      trigger_error( "Bad quote character ($q) at offset 0", E_USER_NOTICE );
      return $s;	
    }
  } 

  /**
  * Collect an encapsed string
  * @param string chunk starting with a quote character
  * @param bool optionally allow line breaks
  * @return string substring containing delimited string
  */
  function collect_encapsed_string( $src, $canbreak = false ){
   if( $canbreak ){
     $pattern = sprintf( '/^%1$s(?:\\\.|[^%1$s\\\])*%1$s/s', $src{0} );
   }
   else {
     $pattern = sprintf( '/^%1$s(?:\\\.|[^\r\n%1$s\\\])*%1$s/', $src{0} );
   }
   if( ! preg_match($pattern, $src, $r ) ){
     return '';
   }
   else {
     return $r[0];
   }
  }
}