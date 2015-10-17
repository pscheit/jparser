<?php

namespace PLUG\functions\PHP;

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

