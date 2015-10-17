<?php

namespace PLUG\compiler;

/**
 * File containing class PHPCompiler
 * @author Tim Whitlock
 * @category PLUG
 * @package compiler
 * @version $Id: PHPCompiler.php,v 1.1 2009/03/22 12:58:58 twhitlock Exp $
 */

 
/**
 * Require miniparser for processing complex include statements
 */ 
use PLUG\compiler\miniparsers\Inc\IncParser;
use PLUG\core\PLUG;
use PLUG\core\PLUGException;
use PLUG\core\PLUGTool;

/**
 * Require path mapping tools
 */
use function PLUG\functions\filesystem\cleanpath;
use function PLUG\functions\filesystem\findpath;
use function PLUG\functions\filesystem\relpath;

/**
 * Require other utils
 */
use function PLUG\functions\filesystem\tempfile;
 
/**
 * Option to keep comments in compiled source, default is off
 */ 
define( 'COMPILER_OPTION_COMMENTS', 1 ); 

/**
 * Option to keep whitespace in compiled source, default is off
 */
define( 'COMPILER_OPTION_WHITESPACE', 2 ); 
 
/**
 * Option to compile source into bytecode, default is off
 */ 
define( 'COMPILER_OPTION_BYTECODE', 4 ); 
 
/**
 * Option to replace config constants with literals, also affects __LINE__ and __FILE__, default is off
 */ 
define( 'COMPILER_OPTION_LITERALS', 8 ); 
 
/**
 * Option to replace system constants with literals, default is off
 */ 
define( 'COMPILER_OPTION_LITERALS_SYS', 16 );
 
/**
 * Option to replace ALL constants with literals, default is off
 */ 
define( 'COMPILER_OPTION_LITERALS_ALL', 24 ); // <- ( 8 | 16 )
 
/**
 * Option to maintain beautiful php tags, i.e. no redundant ones, default is off
 */ 
define( 'COMPILER_OPTION_NICE_TAGS', 32 );

 
 
 
/**
 * Compiles a single top-level php script.
 * @todo optimize repeated loaded of the same conf files 
 * @category PLUG
 * @package compiler
 */
class PHPCompiler {
	
	/**
	 * compiling options
	 * @var int
	 */
	private $options;

	/**
	 * Inclusion directives to compile directly into source
	 * @var array
	 */	
	private $incfuncs;

	/**
	 * Inclusion directives to completely ignore
	 * @var array
	 */	
	private $incnone;
	
	/**
	 * All valid inclusion directives
	 * @var array
	 */	
	private static $allincfuncs = array (
		'include', 'include_once', 'require', 'require_once', 'import', 'virtual','import_return_value'
	);
	 
	/**
	 * @var string
	 */
	private $cwd;

	/**
	 * @var string
	 */
	private $path;
	
	/**
	 * registry of files that have been included
	 * @var array
	 */	
	private $incs;	
	
	/**
	 * registry of dependant files that will need to be processed and deployed
	 * @var array
	 */	
	private $dependencies;		
	
	/**
	 * Target configuration directory name
	 * @var string
	 */	
	private $confname;
	
	/**
	 * Target configuration directory path
	 * @var string
	 */	
	private $confdir;

	/**
	 * Parsed config globals
	 * @var array
	 */	
	private $conf_vars;

	/**
	 * Parsed config constants
	 * @var array
	 */	
	private $conf_consts;

	/**
	 * Flag whether currenly inside php tags
	 * @var bool
	 */	
	private $inphp;	
	
	/**
	 * last token processed, required for cross-file checking of previous input
	 * @var mixed
	 */	
	private $lasttoken;	
	
	/**
	 * File replacement map where compile-time include files may replace development version
	 * @var array
	 */	
	private static $sysfilemap;	 

	/**
	 * Include paths to use when linking runtime libraries
	 * @var array
	 */
	private $incpaths;	

	
	/**
	 * Constructor
	 * @param string
	 * @param int
	 * @param array list of inclusion directives to compile into source code, pass empty array for none, null for all.
	 * @param array list of inclusion directives to completely ignore
	 */
	function __construct( $path, $options, array $incfuncs = null, array $incnone = null ){
		// allow relative paths from host root
		if( $path{0} !== '/' ){
			$path = PLUG_HOST_DIR .'/'. $path;
		}
		if( ! PLUGTool::check_file($path) ){
			throw new PLUGException('bad php path');
		}
		
		$this->path = $path;
		$this->options = $options;
		$this->cwd = dirname( $path );
		
		if( is_null($incfuncs) ){
			$this->incfuncs = self::$allincfuncs;
		}
		else {
			$this->incfuncs = $incfuncs;
		}
		if( is_null($incnone) ){
			$this->incnone = array();
		}
		else {
			$this->incnone = $incnone;
		}
		// allow ignored funcs to override included funcs
		foreach( $this->incfuncs as $i => $incfunc ){
			if( in_array( $incfunc, $this->incnone, true ) ){ 
				unset( $this->incfuncs[$i] );
			}
		}
		
		// build file replacement map for PLUG system files
		if( is_null( self::$sysfilemap ) ){
			self::$sysfilemap = array(
				PLUG_HOST_DIR.'/PLUG/plug.php' => PLUG_HOST_DIR.'/PLUG/plug_c.php',
				PLUG_HOST_DIR.'/PLUG/plugcli.php' => PLUG_HOST_DIR.'/PLUG/plugcli_c.php'
			);
		}
	}	
	
	

	/**
	 * Test option
	 * @param int
	 * @return bool
	 */	
	function opt( $i ){
		return (bool) ($this->options & $i);
	}	
	
	
	
	
	/**
	 * Set target configuration.
	 * @param string name of conf directory
	 * @return void
	 */ 	
	function set_conf( $confname ){
		$this->confname = $confname;
		$this->confdir = PLUG_HOST_DIR.'/'.$confname;
		if( ! PLUGTool::check_dir($this->confdir) ){
			throw new PLUGException('bad conf');
		}
		// parse default plug conf using external parser
		$confpath = $this->confdir.'/PLUG.conf.php';
		$this->conf_vars = array();
		$this->conf_consts = array ();
		$this->load_conf( $confpath );
	}
	
	
	
	
	/**
	 * Parse a given config file and merge values into registry.
	 * Currenly only constants in configs are supported by this compiler.
	 * @todo should be parse variables, or just constants?
	 * @todo use native parser rather than shell out?
	 * @param string path to config file
	 * @return bool
	 */	
	function load_conf( $confpath ){
		if( ! PLUGTool::check_file($confpath) ){
			throw new PLUGException('bad conf');
		}
		$e = PLUG::exec_bin( 'parseconf', array($confpath), $s );
		if( $e ){
			trigger_error('Failed to parse '.basename($confpath), E_USER_NOTICE );
			return false;
		}
		$a = unserialize( $s );
		if( ! is_array($a) ){
			trigger_error( "parseconf returned bad serialize string, $s", E_USER_WARNING );
			return false;
		}
		$this->conf_vars = array_merge( $this->conf_vars, $a[0] );
		$this->conf_consts = array_merge( $this->conf_consts, $a[1] );
		return true;
	}	
	
	
	
	/**
	 * Set include paths to use when linking dependancies.
	 * Recommended setting is ".:plug/inc", although "." will be added automatically anyway
	 * - paths beginning with a dot are assumed to be relative to current script being compiled
	 * - absolute paths must be under development host document root
	 * - other relative references are assumed relative to document root, e.g. `plug/inc'
	 * @param string colon-delimited paths as per include_path ini setting
	 * @return array full local paths set
	 */	
	function set_include_path( $paths ){
		if( ! is_array($paths) ){
			$paths = explode( ':', $paths );
		}
		// should include current location.
		if(  ! in_array('.',$paths,true) && ! in_array($this->cwd,$paths,true) ){
			array_unshift( $paths, '.' );
		}
		// paths should all be on current filesystem
		$this->incpaths = array();
		foreach( $paths as $i => $path ){
			if( $path{0} === '.' ){
				// allow refs relative to self
				$path = cleanpath( $this->cwd.'/'.$path );
			}
			else if( $path{0} !== '/' ){
				// else assume relative to virtual root
				$path = cleanpath( PLUG_VIRTUAL_DIR.'/'.$path );
			}
			// ensure under virtual root
			if( strpos($path,PLUG_VIRTUAL_DIR) !== 0 ){
				trigger_error( "Bad include path `{$paths[$i]}', must be under local virtual root, or relative to it", E_USER_WARNING );
				continue;
			}
			$this->incpaths[] = $path;
		}
		return $this->incpaths;
	}
	
	
	
	/**
	 * Register a file path for inclusion
	 * @param string absolute path
	 * @return bool whether file has already been included
	 */	
	function register_include( $path ){
		if( ! isset($this->incs[$path]) ){
			$this->incs[$path] = 1;
			return false;
		}
		else {
			$this->incs[$path]++;
			return true;
		}
	}
	
	
	
	/**
	 * Register a dependant file for collection.
	 * dependant file must also be compiled, and moved to mapped location.
	 * @param string
	 * 
	 */	
	function register_dependency( $path ){
	
		// register as not having been processed yet
		if( ! isset($this->dependencies[$path]) ){
			$this->dependencies[$path] = 0;
		}
		
		// return path suitable for use in include directive
		// consulting include path settings avoids hard coded paths 
		if( isset($this->incpaths) ){
			$path = PLUG_VIRTUAL_DIR . PLUGTool::map_deployment_virtual( $path );
			return relpath( $this->cwd, $path, $this->incpaths );
		}

		// else we must map this path to the target host with a full path
		$base = $this->conf_consts['PLUG_VIRTUAL_DIR'];
		return $base . PLUGTool::map_deployment_virtual( $path );
	}
	
	
	
	/**
	 * get next dependant file and flag as done
	 * @return string
	 */	
	function next_dependency(){
		foreach( $this->dependencies as $path => $done ){
			if( ! $done ){
				$this->dependencies[$path] = 1;
				return $path;
			}
		}
		// none
		return null;
	}
	
	
	
	/**
	 * Start compilation
	 * @return string optimized source
	 */	
	function compile( $path = null  ){	
		
		// process as root script if path not passed
		$isroot = is_null($path);
		if( $isroot ){
			$path = $this->path;
			$this->incs = array();
			$this->dependencies = array();
		}
		
		// compile initial source for top-level file
		// 
		$this->inphp = false;
		$this->lasttoken = null;
		$src = $this->compile_php( $path, null, $isroot );

		// second pass to replace constants with literals if option set
		//
		if( $this->opt(COMPILER_OPTION_LITERALS)  ){
			$tokens = token_get_all( $src );
			$src = '';
			do {
				$tok = current( $tokens );
				$s = is_array($tok) ? $tok[1] : $tok;
				if( is_array($tok) && T_STRING === $tok[0] ){
					$c = $tok[1];
					if( isset($this->conf_consts[$c]) ){
						// replace current definition with target config value
						$s = var_export( $this->conf_consts[$c], 1 );
					}
					else if( defined($c) && $this->opt(COMPILER_OPTION_LITERALS_SYS) ){
						// WARNING: extension constants could differ on target environment
						$s = var_export( constant($c), 1 );
					}
				}
				$src .= $s;
			}
			while( next($tokens) !== false );
		}

		
		// bytecode compilation with bcompiler extension
		//
		if( $this->opt(COMPILER_OPTION_BYTECODE) ){
			// extension can only operate on files
			$srcpath = tempfile( $stdin, null );
			file_put_contents( $srcpath, $src );
			unset( $src );
			// run via shell due to conflict bugs in bcompiler
			PLUG::exec_bin( 'bcompile', array($srcpath), $src );
		}
		
		return $src;
	}

	


	/**
	 * @internal
	 * @todo whether we should import constants when set COMPILER_OPTION_LITERALS
	 */
	private function compile_import( $package ){
		$src = '';
		$paths = PLUGTool::collect_package( $package, 'php', false, $this->confname );

		foreach( $paths as $path ){
			
			// handle conf file
			if( strpos($path,$this->confdir) === 0 ){
				$this->load_conf( $path );
				// Not importing constants as literals will be used in source
				// WARNING: this will screw up usage like "if( defined() ....
				if( $this->opt(COMPILER_OPTION_LITERALS) ){
					continue;
				}
			}
		
			$src .= $this->compile_php( $path, 'import' );	
		}
		return $src;
	}

	


	/**
	 * @internal
	 * @todo map virtual roots incase differs on target
	 * @todo implement chdir fix
	 * @todo implement plug_virtual wrapper method
	 */
	private function compile_virtual( $vpath ){
		// path should be virtual (or relative to current script?)
		if( $vpath{0} === '/' ){
			$path = PLUG_VIRTUAL_DIR.$vpath;
		}
		else {
			$path = $this->cwd.'/'.$vpath;
		}
		if( ! PLUGTool::check_file($path) ){
			trigger_error("Failed to map virtual path $vpath", E_USER_WARNING );
			return null;
		}
		
		// compile directly into source if option set
		if( in_array( 'virtual', $this->incfuncs, true ) ){
			// do not evaluate php,this is a static include method
			$src = '';
			$this->close_php( $src );
			$src .= file_get_contents( $path );
		}
		
		// else collate for runtime inclusion
		else {
			$this->register_dependency( $path );
			$src = 'virtual( '.var_export($vpath,1).' );';
		}
		return $src;
	}
	


	
	/**
	 * @internal
	 */
	private function compile_php( $path, $incfunc = null, $isroot = false ){
		
		// resolve to absolute path if required
		if( ! $path || $path{0} !== '/' ){
			// if we are not running in the same environment as the development site we may have diffent include paths set, 
			// this is only really likely over CLI, so we shall add common PLUG include paths as an additional parameter.
			if( PLUG_CLI ){
				$extrapaths = array( PLUG_HOST_DIR.'/PLUG' );
			}
			else {
				$extrapaths = null;
			}
			$abspath = findpath( $path, $this->cwd, $extrapaths );
			if( is_null($abspath) ){
				trigger_error( "Failed to map path to $path", E_USER_ERROR );
				return null;
			}
			$path = $abspath;
		}
		
		// replace special system files with compile-time alternatives
		if( isset( self::$sysfilemap[$path] ) ){
			$path = self::$sysfilemap[$path];
		}
		
		// maintain special values in constant & variable registries
		$this->conf_consts['__FILE__'] = $path;
		$this->conf_vars['argv'] = array( 0 => $path );

		if( $incfunc ){
			// Options dictate whether to maintain a runtime inclusion
			if( ! in_array( $incfunc, $this->incfuncs, true ) ){
				$path = $this->register_dependency( $path );
				// convert special inc functions to standard directive
				if( $incfunc == 'import' ){
					$incfunc = 'require_once';
				}
				return "$incfunc ". var_export($path,1).';';
			}

			// test for once only includes
			$included = $this->register_include( $path );
			$onceonly = ( $incfunc == 'import' || $incfunc == 'include_once' || $incfunc == 'require_once' );
			if( $included && $onceonly ){
				// already included once-only file
				return '';
			}
		}
		
		// generate full source code
		$src = '';
		$tokens = token_get_all( file_get_contents($path) );
		
		// write header message, but check for shebang line
		if( ! $incfunc ){
			// allow user-defined header comment from banner.txt file 
			$bannerpath = $this->confdir.'/banner.txt';
			if( file_exists($bannerpath) ){
				$banner = trim( file_get_contents($bannerpath) );
				$lines = preg_split('/(\r\n|\n|\r)/', $banner );
				$lines[] = date('r');
				$header = "/**\n * ".implode("\n * ", $lines )."\n */\n";
			}
			else {
				$header = 
				"/**\n".
			    " * PLUG deployed source code - DO NOT EDIT!\n".
			    " * http://plug.whiteinteractive.com/\n".
			    " * ".date('r')." \n".
			    " */\n";
			}
			if( is_array($tokens) && $tokens[0][1]{0} === '#' ){
				// shebang cannot be preceded
				// add on first T_OPEN_TAG
			}
			else {
				$src = "<?php\n$header";
				unset( $header );
				$this->inphp = true;
			}
		}
		
		
		
		
		// by default, all php files start outide of php tags, but we may be concatenating to a parent file
		// sniff first token so we can close previous inphp state
		if( $this->inphp ){
			$makenice = $this->opt(COMPILER_OPTION_NICE_TAGS);
			$tk = current( $tokens );
			if( $makenice && is_array($tk) && $tk[0] === T_OPEN_TAG ){
				// we do not need this open tag, we are already open
				next( $tokens );
			}
			else {
				// need to close php
				$src .= ' ?>';
				$this->inphp = false;
			}
		}
		
		// process all tokens until file exhausted
		do {
			// run whatever tests you need on last token before overwriting with current token
			// - test if last token was redundant
			static $wstoks = array( T_COMMENT, T_DOC_COMMENT, T_WHITESPACE ) ;
			$prevws = isset($this->lasttoken) && is_array($this->lasttoken) && in_array( $this->lasttoken[0], $wstoks, true );
			
			$tok = current( $tokens );
			$this->lasttoken = $tok;
			
			if( is_scalar($tok) ){
				$src .= $tok;
				continue;
			}

			// else is array token [$t(symbol),$s(value)]
			$t = $tok[0];
			$s = $tok[1];
			switch( $t ){
			
			// maintain state whether escaped out of php or not
			case T_OPEN_TAG:
				$this->open_php( $src );
				if( isset($header) ){
					$src .= "\n".$header;
					unset( $header );
				}
				continue 2;
			
			case T_OPEN_TAG_WITH_ECHO:
				// ?? check this out
				$this->inphp = true;
				$src .= $s;
				continue 2;
				
			case T_CLOSE_TAG:
				$this->close_php( $src );
				continue 2;
			
			// strip whitespace down to single space unless option set to keep it
			case T_WHITESPACE:
				if( $this->opt(COMPILER_OPTION_WHITESPACE) ){
					$src .= $s;
				}
				else if( ! $prevws ){
					// single space required between tokens to avoid parse errors
					$src .= ' ';
				}
				continue 2;
			
			// disard comment unless option set to keep it
			case T_COMMENT:
			case T_DOC_COMMENT:
				if ( $this->opt(COMPILER_OPTION_COMMENTS) ){
					$src .= $s;
				}
				else if( $this->opt(COMPILER_OPTION_WHITESPACE) ){
					$src .= "\n";
				}
				else if( ! $prevws ){
					$src .= ' ';
				}
				continue 2;
			
			// Preserve magic line number constant (PHP >= 5.2.2)
			case T_LINE:
				if( $this->opt(COMPILER_OPTION_LITERALS) ){
					$src .= var_export( $tok[2], 1 );
				}
				else {
					$src .= $s;
				}
				continue 2;
			
			// Preserve magic file path constant
			case T_FILE:
				if( $this->opt(COMPILER_OPTION_LITERALS) ){
					$src .= var_export( $path, 1 );
				}
				else {
					$src .= $s;
				}
				continue 2;
			
			// T_STRING used for various statements, process last.
			case T_STRING:
				if( isset($funcdecl) ){
					// function declaration, not function call
					unset( $funcdecl );
					$src .= $s;
					continue 2;
				}
				if( ! in_array( $s, self::$allincfuncs, true ) ){
					// any old string
					// constants resolved on second pass -
					$src .= $s;
					continue 2;
				}
				// ensure that function directive is global call
				// e.g. import(), not $Something->import()
				// @todo check context of import function call
				
			// else fall through to inclusion directive
			case T_INCLUDE:
			case T_REQUIRE:
			case T_INCLUDE_ONCE:
			case T_REQUIRE_ONCE:
				// we may wish to ignore this directive completely
				if( in_array( $s, $this->incnone, true) ){
					$src .= $s;
					continue 2;
				}
				// else pass to function unless we wish to ignore this directive
				else { 	
					$src .= $this->handle_inc_statement( $tok, $tokens, $path );
				}
				continue 2;
			
			
			// catch function keyword so we know if next T_STRING is a function definition, as opposed to a function call.
			case T_FUNCTION:
				$funcdecl = true;
				$src .= $s;
				continue 2;
			
			// catch any other token
			default:
				$src .= $s;
			}
		}
		while( next($tokens) !== false );
		// done this file
		return $src;
	}
	
	
	
	
	
	
	/**
	 * @internal
	 * @param array token for directive, e.g. [T_STRING,'import']
	 * @param array all tokens with pointer at start of statement
	 * @param bool
	 * @return string source to substitute for statement
	 */	
	private function handle_inc_statement( array $tok, array &$tokens, $path ){
		$func = $tok[1];
		// gather entire statement up to next `;'
		// warning: if you do something like "import() or die();" parsing will fail.
		$src = $tok[1];
		$inctokens = array( $tok );
		$pathargs = array();
		while( $inctok = next($tokens) ){
			$inctokens[] = $inctok;
			$src .= is_scalar($inctok) ? $inctok : $inctok[1];
			if( $inctok === ';' ){
				break;
			}
		}
		// hand off to any special cases
		switch( $func ){
			case 'import_return_value':
				return $this->import_return_value( $src );
		}
		// The remaining directives require compilation of simple string arguments.
		// Attempt parse - will throw on error ...
		$Compiler = new IncParser();
		try {
			$Stat = $Compiler->parse( $inctokens );
			// ensure current __FILE__ reference is correct as it may be referenced in include statement
			$consts = $this->conf_consts;
			$consts['__FILE__'] = $path;
			$pathargs = $Stat->get_args( $consts, $this->conf_vars );
		}
		catch( Exception $Ex ){
			trigger_error(  "Failed to parse `$func' directive in `$path', line {$tok[2]}: ". $Ex->getMessage(), E_USER_WARNING );
			// return empty statement with commented error in case of error
			return sprintf('/* Failed to parse %s directive */;', $func );
		}
		// process all arguments according to directive type
		$src = '';
		foreach( $pathargs as $arg ){
			$arg = cleanpath( $arg );
			switch( $func ){
			
			case 'import':
				$s = $this->compile_import( $arg );
				break;
			
			case 'include':
			case 'require':
			case 'include_once':
			case 'require_once':
				$s = $this->compile_php( $arg, $func );
				break;
			
			case 'virtual':
				$s = $this->compile_virtual( $arg );
				break;
			}

			// handle error if no source returned
			if( is_null($s) ){
				$this->open_php( $src );
				$src .= "$func (".var_export($arg,1)."); // <- failed \n";
			}
			else {
				$src .= $s;
			}
		}
		// handle php state, when we return to where this directive was found, we are expected to still be in php
		$this->open_php( $src );
		return $src;
	}
	
	
	
	/**
	 * Evaluate a function return value for import directly in to source code
 	 * Warning: The function file MUST import all it's dependencies as it will be called at compile time, not at run time.
	 * @param array tokens of full function call
	 */
	private function import_return_value( $src ){
		// evaluate full runtime function call
		eval( '$value = '.$src );
		if( is_object($value) ){
			return 'unserialize('.var_export(serialize($value),1).');';
		}
		else {
			return var_export($value,1).';';
		}
	}
	
	




	/**
	 * Open php tag if required.
	 * @usage <code>$src = $this->open_php( $src );</code>
	 * @param string current php source to modify
	 * @return void
	 */
	private function open_php( &$src ){
		if( ! $this->inphp ){
			$this->inphp = true;
			
			// this may result in back to back tags, which we can avoid with a bit of string manipulation.
			$makenice = $this->opt(COMPILER_OPTION_NICE_TAGS);
			
			if( $makenice && substr( $src, -2 ) === '?>' ){
				// trim trailing close tag, so no need to open one
				$src = substr_replace( $src, '', -2 );
			}
			else {
				$ws = $this->opt(COMPILER_OPTION_WHITESPACE) ? "\n" : ' ';
				$src .= '<?php'.$ws;
			}
		}
		return $src;	
	}


	
	/**
	 * Close php tag if required.
	 * @usage <code>$src = $this->close_php( $src );</code>
	 * @param string current php source to modify
	 * @return void
	 */
	private function close_php( &$src ){
		if( $this->inphp ){
			$this->inphp = false;
			
			// this may result in back to back tags, which we can avoid with a bit of string manipulation.
			$makenice = $this->opt(COMPILER_OPTION_NICE_TAGS);
			
			if( $makenice && substr( $src, -5 ) === '<?php' ){
				// trim trailing open tag, so no need to close
				$src = substr_replace( $src, '', -5 );
			}
			else {
				$src .= ' ?>';
			}
		}
		return $src;	
	}

	
	
	
	
}
	
 
