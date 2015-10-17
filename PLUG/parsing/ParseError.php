<?php

namespace PLUG\parsing;

/**
 * File containing class ParseError
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @version $Id: ParseError.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */

use Exception;

/**
 * ParseError Exception class.
 * @category PLUG
 * @package parsing
 */
class ParseError extends Exception {
	
	/**
	 * Scalara symbol of token
	 * @var string|int
	 */
	public $tokenSymbol;
	
	/**
	 * Name of token
	 * @var string
	 */
	public $tokenName;
	
	/**
	 * @var int
	 */
	public $tokenLine;
	
	/**
	 * @var int
	 */
	public $tokenColumn;
	
	
	/**
	 * @override
	 */
	function __construct( $message, $code, $tokenLine, $tokenColumn, $tokenName, $tokenSymbol ){
		$this->tokenLine = $tokenLine;
		$this->tokenColumn = $tokenColumn;
		$this->tokenName = $tokenName;
		$this->tokenSymbol = $tokenSymbol;
		$s = 'Parse error: ';
		if( $this->tokenName ){
			if( $this->tokenSymbol === P_EOF ){
				$s .= 'premature end of file';
			}
			else if( $this->tokenSymbol && $this->tokenSymbol !== $this->tokenName ){
				$s .= "unexpected $this->tokenName";
			}
			else {
				$s .= "unexpected \"$this->tokenName\"";
			}
		}
		if( $this->tokenLine ){
			$s .= ", line $this->tokenLine";
		}
		if( $this->tokenColumn ){
			$s .= ", column $this->tokenColumn";
		}
		if( $message ){
			$s .= "\n".$message;
		}
		parent::__construct( $s, $code );
	}
	
	
	
	/**
	 * Highlight original source code snippet to show point of error.
	 * @todo improve character counts especially with unicode
	 * @param string original source code
	 * @param int optionally specify number of characters to show before fail point
	 * @param bool whether source is unicode
	 * @return string source snippet
	 */
	function snip( $src, $len = 30, $unicode = false ){
		if( ! $this->tokenLine ){
			return 'Uknown failure point, no line number available';
		}
		if( $unicode ){
			$lines = preg_split('/(\r\n|[\r\n\p{Zl}\p{Zp}])/u', $src );
		}
		else {
			$lines = preg_split('/(\r\n|\n|\r)/', $src );
		}
		$line = $this->tokenLine - 1;
		if( ! isset($lines[$line]) ){
			return 'Unknown failure point, line '.$this->tokenLine.' not in source';
		}
		$src = $lines[$line];
		if( ! $this->tokenColumn ){
			return "near: ".substr( $src, 0, $len )."\nNo column number available";
		}
		$start = max( 0, $this->tokenColumn - $len );
		$offset = ( $this->tokenColumn - $start ) - 1;
		$pad = max( 0, $offset - 1 );
		$src = substr( $src, $start, $len )."\n".str_repeat('.',$pad).'^';
		// replace tabs with spaces for better formatting 
		$src = str_replace( "\t", ' ', $src );
		return $src;
	}
	
	
	
	
	
	
	
	
	
	
}
