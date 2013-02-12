<?php

namespace PLUG;

/**
 * Utility function formats a number of milliseconds into larger denominations
 * @param int milliseconds
 * @return string
 */	 
function millisecond_format( $ms ){
	// format as milliseconds if less than a second
	if( $ms <= 1000 ){
		return sprintf( '%d milliseconds', $ms );
	}
	// format as decimal seconds if less than a minute
	if( $ms < 60000 ){
		return sprintf( '%f seconds', $ms / 1000 );
	}
	// else format as hh:mm:ss
	$h = floor( $ms / 3600000 );
	$ms -= $h * 3600000;
	$m = floor( $ms / 60000 );
	$ms -= $m * 60000;
	$s = round( $ms / 1000 );
	return sprintf('%02d:%02d:%02d', $h, $m, $s );
}

/**
 * Utility function formats a number of bytes in larger denominations
 * @param int
 * @return string
 */	 
function memory_format( $n ){
	$units = array( 'bytes', 'Kb', 'Mb', 'Gb', 'Terabytes', 'Petabytes' );
	$i = 0;
	$dp = 0;
	while( $n >= 1024 ){
		$i++;
		$dp++;
		$n /= 1024;
	}
	$s = number_format( $n, $dp, '.', ',' );
	return sprintf("%s %s", $s, $units[$i] );
}

/**
 * Replacement as this function was removed from PHP as from 5.0.5
 * @example functions/PHP/php_check_syntax.php
 * @param string file path
 * @param string reference for error description
 */
function php_check_syntax( $filepath, &$err ){
	$command = 'php -l '.escapeshellarg($filepath). ' 2>&1';
	exec( $command, $r, $e );
	if( $e !== 0 ){
		$err = implode( "\n", $r );
		return false;
	}
	return true;
}	

/**
 * Complex split operation of delimited list of optionally quoted values.
 * @example functions/string/csv_split.php See example usage
 * @param string Delimited and quoted string to split into array
 * @param string Optional value separator; defaults to ",".
 * @param bool Optional unescape flag; defaults to "\"
 * @return array split list of values
 */
function csv_split( $src, $comma = ',', $esc = '\\' ){
	$a = array();
	while( $src ){
		$c = $src{0};
		switch( $c ){
		// permit empty values
		case ',':
			$a[] = '';
			$src = substr( $src, 1 );
			continue 2;
		// ignore whitespace
		case ' ':
		case "\t":
			preg_match('/^\s+/', $src, $r );
			$src = substr( $src, strlen($r[0]) );
			continue 2;
		// quoted values
		case '"':
		case "'":
		case '`':
			$reg = sprintf('/^%1$s((?:\\\\.|[^%1$s\\\\])*)%1$s\s*(?:,|$)/', $c );
			break;
		// naked values
		default:
			$reg = '/^((?:\\\\.|[^,\\\\])*)(?:,|$)/';
			$c = ',';
		}
		if( preg_match( $reg, $src, $r ) ){
			$a[] = empty($r[1]) ? '' : str_replace( '\\'.$c, $c, $r[1] );
			$src = substr( $src, strlen($r[0]) );
			continue;
		}
		// else fail
		trigger_error("csv_split failure", E_USER_WARNING );
		break;
	}
	return $a;
}

/**
 * Provides neater view of debug_backtrace();
 * @package functions
 * @subpackage PHP
 * @param bool optionally force plain text.
 */
function stack_dump( $plaintext = false ){
	$stack = array_slice( debug_backtrace(), 1 );
	foreach( $stack as $i => $callee ){
		// what's been called.
		if( isset($callee['class']) ){
			$call = $callee['class'].$callee['type'].$callee['function'];
		}
		else{
			$call = $callee['function'];
		}
		// file:line may not always be available
		if( isset($callee['file']) ){
			$fileinfo = 'in '. str_replace(PLUG_HOST_DIR,'',$callee['file']).' line '.$callee['line'];
		}
		else{
			$fileinfo = 'in unknown file';
		}
		// simplify complex arguments to avoid massive dump.
		$args = array();
		foreach( $callee['args'] as $arg ){
			if( is_object($arg) ){
				$args[] = "object ".get_class($arg);
			}
			else if( is_array($arg) ){
				$args[] = "array(".count($arg).")";
			}
			else{
				if( is_string($arg) ){
					$arg = str_replace( array("\n","\r"), array('\n','\r',), $arg );
				}
				$args[] = var_export( $arg, true );
			}
		}
		// show this layer.
		printf( '#%02d. ', $i );
		if( ! $plaintext ){
			echo "<br />\n  ";
			highlight_string("<? $call ( ". implode(', ',$args)." ) ?>");
			echo "<br />\n  $fileinfo <br />--<br />\n";
		}
		else{
			echo "\n  ";
			echo "$call ( ". implode(', ',$args)." )";
			echo "\n  $fileinfo \n--\n";
		}
	}
}

/**
 * Replacement for {@link highlight_string} using class names instead of font colours
 * @example functions/PHP/php_highlight_string.php
 * @param string source code
 * @return string html source
 */
function php_highlight_string( $src ){
	// convert non-unix line breaks
	$src = str_replace (
		array( "\r\n", "\r" ),
		array( "\n", "\n" ),
		$src
	);
	
	$tokens = token_get_all( $src );
	$lines = array('');
	$quot = '';
	foreach( $tokens as $token ){
		if( is_array($token) ){
			list($t,$s) = $token;
			$class = token_name($t);
			// categorize token into broader class
			switch( $t ){
			case T_ECHO:
			case T_SWITCH:
			case T_BREAK:
			case T_RETURN:
			case T_FOR:
			case T_FOREACH:
			case T_REQUIRE:
			case T_REQUIRE_ONCE:
			case T_INCLUDE:
			case T_INCLUDE_ONCE:
			case T_class:
			case T_PUBLIC:
			case T_PRIVATE:
			case T_PROTECTED:
			case T_FUNCTION:
			case T_STATIC:
			case T_FINAL:
			case T_ABSTRACT:
			case T_IF:
			case T_ELSE:
			case T_ISSET:
			case T_UNSET:
			case T_EMPTY:
				$class .= ' PHP_KEYWORD';
				break;
			}
			if( $quot || $t === T_CONSTANT_ENCAPSED_STRING ){
				$class .= ' PHP_QUOTED';
			}
			while( $s !== '' ){
				if( ! preg_match('/^(.*)\n/', $s, $r ) ){
					$lines[0] .= '<span class="'.$class.'">'._php_highlight_string_escape($s).'</span>';
					break;
				}
				$lines[0] .= '<span class="'.$class.'">'._php_highlight_string_escape($r[1]).'</span>';
				array_unshift( $lines, '' );
				$s = substr( $s, strlen($r[0]) );
			}
		}
		else {
			if( $quot ){
				$class = 'PHP_QUOTED';
			}
			else {
				$class = '';
			}
			if( $quot && $token === $quot ){
				// end quote
				$quot = '';
			}
			else if( $token == "'" || $token === '"' ){
				// start quote
				$quot = $token;
				$class = 'PHP_QUOTED';
			}
			if( $class ){
				$lines[0] .= '<span class="'.$class.'">'._php_highlight_string_escape($token).'</span>';
			}
			else {
				$lines[0] .= _php_highlight_string_escape($token);
			}
		}
	}
	$src = '</ol>';
	foreach( $lines as $i => $s ){
		$class = $i & 1 ? 'odd' : 'even';
		$src = "<li class=\"$class\">$s</li>\n$src";
	}
	return "<ol class=\"php\">\n$src";
}	



/**
 * @internal
 * Escape source code to HTML
 */
function _php_highlight_string_escape( $s ){
	if( $s === '' ){
		return '&nbsp;';
	} 
	return str_replace ( 
		array(' ', "\t" ), 
		array('&nbsp;','&nbsp;&nbsp;&nbsp;&nbsp;'), 
		htmlspecialchars( $s, ENT_COMPAT, 'ISO-8859-1' ) 
	);
}

/**
 * Neaten path with dots and extra slashes etc.
 * Use instead of {@link realpath} to avoid resolution of symlinks
 * @example functions/filesystem/cleanpath.php
 * @param string original path
 * @return string clean path
 */	
function cleanpath( $path ){
	if( ! $path ){
		return '';
	}
	$source = explode( '/', $path );
	$target = array_splice( $source, 0, 1 );
	foreach( $source as $i => $f ){
		if( $f === '.' || $f === '' ){
			// redundant reference to self
		}
		else if( $f === '..' ){
			// up a level if possible
			$f = end( $target );
			if( ! $f || $f === '..' ){
				// check it hasn't gone above root
				if( $path{0} === '/' ){
					trigger_error('Path goes above root', E_USER_NOTICE );
					return '/';
				}
				// else we have to keep the ..
				$target[] = '..';
			}
			// else ok to drill up.
			else {
				array_pop( $target );
			}
		}
		else {
			$target[] = $f;
		}
	}
	$cpath = implode('/', $target );
	if( $cpath === '' && $path{0} === '/' ){
		return '/';
	}
	return $cpath;
}

/**
 * Utility function formats a string file size description
 * @param string
 * @return string
 */	 
function file_size( $filepath ){
	$n = (float) filesize( $filepath );
	return memory_format( $n );
}

/**
 * Simple interface to shell find program.
 * - Useful for listing directoried recursively with a simple search pattern.
 * - This is much faster than {@see preg_find} use it if you dont't require regexp matching.
 * @todo support other flags other than -name
 * @param string path to search under
 * @param string simple search pattern, e.g. *.php
 */
function find( $path, $name ){
	$command = 'find '.escapeshellarg($path).' -name '.escapeshellarg($name);
	exec( $command, $lines, $e );
	if( $e ){
		trigger_error("find exited $e, ".implode("\n",$lines), E_USER_WARNING );
		return array();
	}
	return $lines;
}

/**
 * Collects files according to a regular expression.
 * - Useful for listing directories recursively.
 * - This is much slower than {@see find}, because it uses {@see readdir} and {@see opendir}
 * - Note that expression only acts on file names, and not directories
 * @param string path to search under
 * @param string preg_match compatible regular expression, e.g. /\.php$/
 * @param bool whether to descend into sub directories, defaults to false
 * @return array
 */
function preg_find( $path, $pattern, $recursive = false ){
	$found = array();
	_preg_find_recursive( $path, $pattern, $recursive, $found );
	return $found;
}



/**
 * @ignore
 */
function _preg_find_recursive( $path, $pattern, $recursive, array &$found ){
	$dhandle = opendir( $path );
	if( ! is_resource($dhandle) ){
		return;
	}
	while( false !== $f = readdir($dhandle) ){
		if( $f === '.' || $f === '..' ){
			continue;
		}
		$nextpath = $path.'/'.$f;
		// recurse depth first into directories
		if( is_dir($nextpath) ){
			if( $recursive ){
				_preg_find_recursive( $nextpath, $pattern, $recursive, $found );
			}
		}
		// collect if no pattern passed
		if( ! $pattern ){
			$found[] = $nextpath;
		}
		// else collect file if pattern matches
		else if( preg_match( $pattern, $f ) ){
			$found[] = $nextpath;
		}
	}
	closedir($dhandle);
}

/** require path cleaning function */
use PLUG\functions\filesystem\cleanpath;
 
 
/**
 * Map a relative path from a working directory to a target file
 * - this will always return a relative path, even if target is in the root
 * @example functions/filesystem/relpath.php
 * @param string absoulte path to current working directory
 * @param string absolute path to target 
 * @param array optional include paths, this setting overrides the following.
 * @param bool optionally ommit leading single dot, e.g. './here' becomes 'here'
 * @return string relative path to target from cwd
 */	
function relpath( $thisdir, $target, array $incs = null, $nodot = false ){

	if( $target{0} !== '/' ){
		// target already relative
		return $target;
	}
	else if( $thisdir{0} !== '/' ){
		trigger_error( "first argument must be an absolute path", E_USER_NOTICE );
		return $target;
	}
	
	// important: this method will fail if paths have redundant references
	$thisdir = cleanpath( $thisdir );
	$target = cleanpath( $target );
	
	// support include paths, as base directory
	if( ! empty($incs) ){
		$paths = array();
		foreach( $incs as $inc ){
			if( $inc{0} !== '/' ){
				$inc = $thisdir.'/'.$inc;
			}
			$inc = cleanpath( $inc );
			$nodot = $inc !== $thisdir;
			$relpath = relpath( $inc, $target, null, $nodot );
			// log path by length
			$paths[ strlen($relpath) ] = $relpath;
		}
		// return shortest path calculated
		ksort( $paths );
		return current( $paths );
	}
	
	$filename = basename( $target );
	$athis = explode('/', $thisdir );
	$atarg = explode('/', dirname($target) );

	// at some point paths will branch, that's our common point.
	while( ! empty($athis) && ! empty($atarg) ){
		$fthis = $athis[0];
		$ftarg = $atarg[0];
		if( $ftarg !== $fthis ){
			// paths branch at this point
			break;
		}
		array_shift( $athis );
		array_shift( $atarg );
	}
	// target could be in the root
	$inroot = empty($atarg) || $atarg === array('');
	// target is below cwd if athis is empty
	if ( empty($athis) ){
		$apath = $nodot ? array() : array('.');
	}
	// else navigate up to common directory
	else {
		$apath = array_fill( 0, count($athis), '..' );
	}
	// drill down to target, unless relpath targets root
	if( ! $inroot ){
		$apath = array_merge( $apath, $atarg );
	}
	// append file name and we're there!
	$apath[] = $filename;
	return implode( '/', $apath );		
}

/**
 * Resolve a given path against currently defined include paths
 * @param string unresolved relative path
 * @param string optional current working directory
 * @param array optional additional paths to search
 * @param bool optionally avoid using include_path ini setting
 * @return string full path to resolved location
 */
function findpath( $f, $cwd = '.', array $extra = null, $noini = false ){
	if( $f == '' ){
		return $cwd;
	}
	if( $f{0} === '/' ){
		return cleanpath( $f );
	}
	$incs = $noini ? array() : explode( ':', ini_get('include_path') );
	if( ! is_null($extra) ){
		$incs = array_merge( $incs, $extra );
	}
	foreach( $incs as $inc ){
		if( $inc === '.' ){
			// current dir
			$inc = $cwd;
		}
		else if( $inc{0} !== '/' ){
			// relative path as include path, bad idea
			$inc = $cwd . $inc;
		}
		if( $f{0} !== '/' && substr($inc,-1,1) !== '/' ){
			// glue required
			$path = $inc . '/' . $f;
		}
		else {
			// already has glue
			$path = $inc . $f;
		}
		if( file_exists( $path ) ){
			return cleanpath( $path );
		}
	}
	// no path matched
	return null;
}

/**
 * Improvement to php tmpfile function because it provides us with file path via tempnam
 * @example functions/filesystem/tempfile.php
 * @param resource reference to file pointer variable
 * @param string optional fopen mode, defaults to "w", pass empty to avoid opening file
 * @param string optional prefix for tempnam, defaults to "plug"
 * @param bool optionally unlink file on shutdown, defaults to true 
 * @return string path to tmp file
 */	
function tempfile( &$rsc, $mode = 'w', $prefix = 'plug', $unlink = true ){
	if( is_resource($rsc) ){
		trigger_error( "You should pass an empty reference to tempfile ($rsc)", E_USER_WARNING );
		fclose( $rsc );
	}
	$path = tempnam( PLUG_TMP_DIR, $prefix );
	if( !$path ){
		throw new Exception("tempnam failed in tempfile");
	}
	if( $unlink ){
		register_shutdown_function( 'unlink', $path );
	}
	if( $mode ){
		$rsc = fopen( $path, $mode );
		if( ! is_resource($rsc) ){
			throw new Exception("fopen failed in tempfile with mode $mode");
		}
	}
	return $path;
}
