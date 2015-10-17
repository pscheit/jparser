<?php

namespace PLUG\core;

/**
 * File containing PLUGTool class
 * @category PLUG
 * @package core
 */




/**
 * Helper class for executing original uncompiled source code.
 * - Supports importing objects into PLUG environment.
 * - This class is only required in development mode and is not included in compiled source
 * @example core/PLUGTool.php
 * @category PLUG
 * @package core
 */
final class PLUGTool {


	/**
	 * Cache of directory listings to aid performance
	 * @var array
	 */
	private static $dircache = array();

	

	/**
	 * Import PHP classes and functions into the runtime environment
	 * @param string
	 * @param bool whether to allow silent failure
	 * @return Void
	 */	
	static function import_php( $package, $silent = false ){
		
		$type = current( explode('.', $package) );
		$paths = self::collect_package( $package, 'php', $silent, 'conf' );
		
		if( !$silent && empty($paths) ){
			trigger_error("Bad import `$package'", E_USER_ERROR );
		}
		
		foreach( $paths as $cname => $path ){
			// leniant inclusion if silent
			if( $silent ){
				include_once $path;
				continue;
			}
			// path should already be validated by collect_package
			require_once $path;
			// Run checks according to type of import
			switch( $type ){
			case 'conf':
				break;
			default:
				// Class or function import must define an entity with the same name as the file
				// testing function first to avoid call to __autoload
				if( ! function_exists($cname) && ! class_exists($cname) ){
					trigger_error( "Class, or function '$cname' not defined in file `$path'", E_USER_ERROR );
				}
			}
		}
	}
	
	
	
	
	/**
	 * Collect files from import argument
	 * @param string package identifier, e.g. `PLUG.core.*' 
	 * @param string file extension to match
	 * @param bool whether to allow silent failure
	 * @param string alternative config directory
	 * @return array
	 */	
	static function collect_package( $package, $ext, $silent, $confname ){
	
		// get from cache for speed
		$cachekey = $package.'.'.$ext;
		if( isset(self::$dircache[$cachekey]) ){
			return self::$dircache[$cachekey];
		}

	
		$apath = explode( '.', $package );
		$type = $apath[0];   // e.g. "PLUG"
		$cname = array_pop( $apath );      // e.g. "PLUGSession"
	
		// force special extensions, in certain cases
		switch( $type ){
		case 'conf':
			$ext = 'conf.php';
			break;
		}
		
		// special rules for types of import
		switch( $ext ){
		case 'js':
			// Javascript under document root
			$dpath = PLUG_VIRTUAL_DIR.'/plug/js/'.implode( '/', $apath );
			break;
		case 'conf.php':
			// replace with target conf dir
			$apath[0] = $confname; 
			// fall through ....
		default:
			// regular PHP import from outside document root
			$dpath = PLUG_HOST_DIR .'/'. implode( '/', $apath );
		}
		
		$incs = array();	
	
		switch( $cname ){
		case '':
		case '*':
		//  import all files under directory
			if( !$silent && !self::check_dir( $dpath ) ){
				break;
			}
			$dhandle = opendir( $dpath );
			while( $f = readdir($dhandle) ){
				// skip dot files
				if( $f{0} === '.' ){
					continue;
				}
				$i = strpos( $f, ".$ext" ); 
				if( $i ){
					// file has correct extension
					if( substr($f,0,2) === '__' ){
						// skip file name starting "__"
						continue;
					}
					$cname = substr_replace( $f, '', $i );
					$incs[$cname] = "$dpath/$f";
				}
			}
			closedir($dhandle);
			break;
		
		default:
		//  assume single file exists with expected extension
			$path = "$dpath/$cname.$ext";
			if( !$silent && !self::check_file($path) ){
				break;
			}
			$incs[$cname] = $path;
		}
		
		// cache for next time
		self::$dircache[$cachekey] = $incs;
		return $incs;
	}	 	
	

	
	/**
	 * Collect files from a directory
	 * @param string directory path
	 * @param bool whether to recurse into directories
	 * @param string optional match pattern
	 * @param string optional ignore pattern (match overrides)
	 * @return array absolute paths collected
	 */	
	static function collect_files( $dpath, $r = false, $match = null, $ignore = null ){
		$paths = array();
		$dhandle = opendir( $dpath );
		while( $f = readdir($dhandle) ){
			if( $f === '.' || $f === '..' ){
				continue;
			}
			// pattern tested only on file name
			// ignore pattern applies to directories as well as files
			if( isset($ignore) && preg_match($ignore, $f) ){
				continue;
			}
			$path = $dpath.'/'.$f;
			if( is_dir($path) ){
				if( $r ){
					$paths = array_merge( $paths, self::collect_files($path, true, $match, $ignore) );
				}
			}
			// test match requirement on files only
			else if( isset($match) && ! preg_match($match, $f) ){
				continue;
			} 
			// else file ok to collect
			else {
				$paths[] = $path;
			}
		}
		closedir($dhandle);
		return $paths;
	}
	

	
	
	/**
	 * Utility function to check resource can be read.
	 * - Include paths will not be evaluated.
	 * - E_USER_NOTICE raised on failure.
	 * @param string Full path to file
	 * @param bool whether directory write permission to be checked
	 * @param bool Whether file is expected to be a directory
	 * @return bool False if file cannot be read or does not exist
	 */	
	static function check_file( $path, $w = false, $isdir = false ){
	 	$strtype = $isdir ? 'Directory' : 'File'; 
		if( !file_exists($path) ){
			trigger_error("$strtype not found; `$path'", E_USER_NOTICE );
			return false;
		}
		if( $isdir && !is_dir($path) ){
			trigger_error("Not a directory; `$path'", E_USER_NOTICE );
			return false;
		}
		if( !$isdir && !is_file($path) ){
			trigger_error("Not a file; `$path'", E_USER_NOTICE );
			return false;
		}
		if( !is_readable($path) ){
			trigger_error("$strtype not readable by `".trim(`whoami`)."'; `$path'", E_USER_NOTICE );
			return false;
		}
		if( $w && !is_writeable($path) ){
			trigger_error("$strtype not writeable by `".trim(`whoami`)."'; `$path'", E_USER_NOTICE );
			return false;
		}
		return true;
	 }
	 
	 
	 
	 
	 /**
	  * Shortcut to compiler_check_file with $isdir=true
	  * @param string Full path to directory
	  * @param bool whether directory write permission to be checked
	  * @return Boolean False if file cannot be read or does not exist
	  */
	 static function check_dir( $path, $w = false ){
	 	return self::check_file( $path, $w, true );
	 }
	 
	 
	 
	
	/**
	 * Map source file location to a virtual path under document root.
	 * @param string absolute path to development file
	 * @return string virtual path
	 */	 
	static function map_deployment_virtual( $path ){
		// if path is outside document root move to special plug include dir
		if( strpos( $path, PLUG_VIRTUAL_DIR ) !== 0 ){
			if( strpos( $path, PLUG_HOST_DIR ) === 0 ){
				// is under host root	
				$len = strlen(PLUG_HOST_DIR) + 1;
				$subpath = substr_replace( $path, '', 0, $len );
			}
			else{
				// else could be anywhere in filesystem
				$subpath = md5( dirname($path) ).'/'.basename($path);
			}
			return '/plug/inc/'.$subpath;
		}
		// else just remove document root
		return str_replace( PLUG_VIRTUAL_DIR, '', $path );
	}	
		
	
}




/**
 * Global function for importing PLUG PHP entities.
 * @usage <code>import('PLUG.example.*');</code>
 * @param string dot-delimited package description
 * @return Void
 */ 
function import( $package ){
	return PLUGTool::import_php( $package );
}



/**
 * Global function for importing a function return value.
 * Warning: The function file MUST import all it's dependencies as it will be called at compile time, not at run time.
 * @param string dot-delimited path to function
 * @param array function arguments
 * @param bool optionally disable static caching of return value
 * @return mixed the value return from the specified function
 */
function import_return_value( $package, array $args, $nocache = false ){
	static $cache = array();
	if( ! $nocache ){
		$key = $package.'.'.serialize($args);
		if( array_key_exists( $key, $cache ) ){
			return $cache[ $key ];
		}
	} 
	import( $package );
	$a = explode( '.', $package );
	$func = end( $a );
	$value = call_user_func_array( $func, $args );
	// cache value if required
	if( isset($key) ){
		$cache[$key] = $value;
	}
	return $value;
}



/**
 * Dummy autoloader
 * @ignore
 */
function __autoload( $cname ) {
	//trigger_error( "The PLUG framework does not like __autoload ('$cname')", E_USER_ERROR );
}


