<?php

namespace PLUG\parsing;

/**
 * File containing class GrammarBuilder
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @version $Id: GrammarBuilder.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */


use PLUG\parsing\Grammar;


/**
 * Grammar utility class for defining a grammar
 * @example parsing/GrammarBuilder.php
 * @category PLUG
 * @package parsing
 */
class GrammarBuilder extends Grammar {
	
	
	/**
	 * Convert a hard-coded grammar into a Grammar instance
	 * @param array grammar in form [ A => [ [abc],[def] ], B => ...
	 * @param string optionally allow instantiation of a Grammar subclass
	 * @return Grammar
	 */	
	static function make( array $grammar, $class = __CLASS__ ){
		$Me = new $class;
		foreach( $grammar as $nt => $rhss ){
			foreach( $rhss as $rhs ){
				$Me->make_rule( $nt, $rhs );
			}
		}
		return $Me;
	}
	
	
	
	/**
	 * Get/Set start symbol
	 * @param int
	 * @return int
	 */ 	
	function start_symbol( $newss = null ){
		$oldss = $this->goal;
		if( ! is_null($newss) ){
			$this->goal = $newss;
			// special wrapper rule
			$this->rules[ -2 ] = array( P_GOAL, array( $newss, P_EOF ) );
			$this->rebuild_index();
		}
		return $oldss;
	}
	
	
	
	/**
	 * Rebuild index 
	 * @return void
	 */	
	private function rebuild_index(){
		$this->uindex = array();
		$this->ntindex = array();
		foreach( $this->rules as $i => $rule ){
			list($nt,$rhs) = $rule;
			$sig = self::rule_signature( $nt, $rhs );
			$this->uindex[$sig] = $i;
			$this->ntindex[$nt][] = $i;		
		}
	}

	
	
	/**
	 * @override
	 */	
	function get_rules( $nt ){
		if( ! isset($this->ntindex) ){
			$this->rebuild_index();
		}
		return parent::get_rules( $nt );
	}
	
	
	
	/**
	 * Create a signature to identify a rule uniquely
	 * @param mixed scalar non-terminal symbol
	 * @param array list of symbols in right hand side
	 * @return string
	 */	
	private static function rule_signature( $lhs, array $rhs ){
		$a[] = $lhs;
		foreach( $rhs as $t ){
			$a[] = $t;
		}
		return implode( '.', $a );
	}	

	
	
	
	/**
	 * Factory method, ensures unique rules
	 * @param mixed scalar non-terminal symbol
	 * @param array list of symbols in right hand side
	 * @return array
	 */
	function make_rule( $nt, array $rhs ){
		
		if( ! isset($this->uindex) || ! isset($this->ntindex) ){
			$this->rebuild_index();
		}
		
		// explicit goal rule ??
		if( end($rhs) === P_EOF ){
			// trigger_error("Do not specify P_EOF explicitly in grammar", E_USER_WARNING );
			$this->goal = $nt;
			$this->rules[ -2 ] = array( P_GOAL, array( $nt ) );
			$this->rebuild_index();
		}
		// guess start symbol as first in grammar
		else if( is_null( $this->start_symbol() ) ){
			$this->start_symbol( $nt );
		}
		
		reset( $rhs );
		$sig = self::rule_signature( $nt, $rhs );
		
		// return existing if possible
		if( isset($this->uindex[$sig]) ){
			return $this->rules[ $this->uindex[$sig] ];
		}
		// else create new rule
		$rule = array( $nt, $rhs );
	
		// register globally
		$this->rules[ $this->i ] = $rule;
		$this->uindex[$sig] = $this->i;
		$this->ntindex[$nt][] = $this->i;
		$this->i += 2;
	
		// register symbols
		if( isset($this->ts[$nt] ) ){
			// not terminal after all
			unset( $this->ts[$nt] );
		}
		$this->nts[ $nt ] = $nt;
		// assume rhs all terminals until identified as not
		foreach( $rhs as $s ){
			is_array( $s ) and $s = $s[0];
			if( !isset($this->nts[$s]) ){
				$this->ts[ $s ] = $s;
			}
		}
		return $rule;
	}
	
	
	
	/**
	 * Remove a non-terminal symbol and delete all of its rules.
	 * @param int
	 * @return void
	 */
	function remove_rules( $nt ){
		if( ! isset($this->ntindex) ){
			$this->rebuild_index();
		}
		// remove this non-terminal's rules
		if( isset($this->ntindex[$nt]) ){
			foreach( $this->ntindex[$nt] as $i ){
				$sig = self::rule_signature( $nt, $this->rules[$i][1] );
				unset( $this->rules[$i] ); 
				unset( $this->uindex[$sig] );
			}
		} 
		// remove non terminal
		unset ( 
			$this->nts[$nt],
			$this->ntindex[$nt]
		);
	}	

	
	
	/**
	 * @override
	 * invoke lazy creation of first sets
	 */
	function first_set( array $sequence ){
		if( ! isset($this->firsts) ){
			$this->firsts = $this->build_first_sets();
		}
		return parent::first_set( $sequence );
	}		
	
	
   /**
	* Generate FIRST sets for each non-terminal.
	* @return array
	*/	
	private function build_first_sets(){
		$firsts = array();
		do {
			$changes = 0;
			foreach( $this->rules as $rule ){
				list( $nt, $rhs ) = $rule;
				$s = reset( $rhs );
				// get any special rule processing for this non-terminal
				//$excludela = isset($this->excludela[$nt]) ? $this->excludela[$nt] : null;
				do {
					// add terminal to first set of $nt
					if( $this->is_terminal( $s ) ){
						if( ! isset($firsts[$nt][$s]) ){
							$firsts[$nt][$s] = $s;
							$changes++;
						}
						continue 2;
					}
					// else inherit from first set of this non-terminal
					if( ! isset($firsts[$s]) ){
						// throw new Exception( sprintf("FIRST(%s) not defined", $s) );
						$changes++;
						continue 2;
					}
					// collect all terminal symbols in this non-terminal's set
					// except the empty string which we do not inherit.
					$derives_e = isset( $firsts[$s][P_EPSILON] );
					foreach( $firsts[$s] as $t ){
						// Omit any lookahead symbols denied by special rule
						//if( $excludela && in_array( $t, $excludela,true ) ){
						//	// ignored
						//}
						//else 
						if( $t !== P_EPSILON && ! isset( $firsts[$nt][$t] ) ){
							$firsts[$nt][$t] = $t;
							$changes++;
						}
					}
					// we move to next in sequence if non-terminal can derive empty 
				}
				while( $derives_e && $s = next($rhs) );
			}
		}
		while( $changes > 0 );
		return $firsts;
	}	
	
	
	
	/**
	 * @override
	 * invoke lazy creation of follow sets
	 */
	function follow_set( $s ){
		if( ! isset($this->follows) ){
			$this->follows = $this->build_follow_sets();
		}
		return parent::follow_set($s);
	}	
	
	
	
	
   /**
	* Generate FOLLOW sets for each non-terminal.
	* @todo fix inheritance problem
	* @return array
	*/	
	private function build_follow_sets(){
		if( ! isset($this->firsts) ){
			$this->firsts = $this->build_first_sets();
		}
		// remember inhritance for error checking
		//$inherits = array();
		// create default/dummy sets with special symbols
		$follows = array(
			P_GOAL => array(),
			P_EOF => array()
		);
		do {
			$changes = 0;
			foreach( $this->rules as $rule ){
				list( $s, $rhs ) = $rule;
				//echo "----\n";
				//echo "$s -> ", implode(',', $rhs), "\n";
				while( $a = current($rhs) ){
					if( $a === P_EPSILON ){
						next($rhs);
						continue;
					}
					//echo "\na = $a\n";
					if( false === next($rhs) ){
						// end of rule, inherit from LHS
						if( !isset($follows[$s]) ){
							//echo "FOLLOW($s) not set, required by $a \n";
							//$inherits[$s] = true;
							continue 2;
						}
						foreach( $follows[$s] as $t ){
							if( !isset($follows[$a][$t]) ){
								//echo "FOLLOW($a) []= $t \n";
								$follows[$a][$t] = $t;
								$changes++;
							}
						}
						// next rule.
						continue 2;
					}

					$r = array_slice( $rhs, key($rhs) );
					while( $b = current($r) ){
						//echo "b = $b\n";
						// merge first(b) into follow(a), 
						// if it derives the empty string, continue to next in rhs
						$fs = $this->is_terminal($b) ? array($b) : $this->firsts[$b];
						//echo "FOLLOW($a) []= FIRST($b) = ",implode(',', $fs),"\n";
						foreach( $fs as $t ){
							if( ! isset($follows[$a][$t]) && $t !== P_EPSILON ){
								$follows[$a][$t] = $t;
								//echo "FOLLOW($a) []= $t \n";
								$changes++;
							}
						}
						// if derives empty, skip to next or inherit lhs
						if( ! isset($fs[P_EPSILON]) ){
							break;
						} 
						if( false === next($r) ){
							//echo "FOLLOW($a) []= FOLLOW($s)\n";
							if( !isset($follows[$s]) ){
								//echo "FOLLOW($s) not set, required by $a \n";
								//$inherits[$s] = true;
								continue 3;
							}
							foreach( $follows[$s] as $t ){
								if( !isset($follows[$a][$t]) ){
									//echo "FOLLOW($a) []= $t \n";
									$follows[$a][$t] = $t;
									$changes++;
								}
							}
						}
					}
				}
			}
		}
		while( $changes );
		// check inheritance problems, uncomment to debug
		/*
		foreach( $inherits as $s => $bool ){
			if( ! isset($follows[$s]) ){
				trigger_error("FOLLOW($s) was never created", E_USER_NOTICE );
			}
		} 
		//*/
		return $follows;
	}
	
	
	
	
	/**
	 * print out a subclass definition with this object's members embedded
	 * @param string name of extended class
	 * @param array optional doc block meta data, e.g. [ 'package' => 'parsing'  ]
	 * @return void
	 */	
	function class_export( $classname, array $commentData = array() ){
		echo 
		"/**\n * Auto-generated file containing class $classname";
		foreach( $commentData as $tag => $value ){
			echo "\n * @$tag $value";
		}
		echo 
		"\n */\n",
		"\n",
		"use PLUG\parsing\Grammar;\n",
		"\n",
		"/**\n * Auto-generated Grammar subclass";
		// unset page level doc block tags we don't want against the class
		unset (
			$commentData['author'], $commentData['version']
		);
		foreach( $commentData as $tag => $value ){
			echo "\n * @$tag $value";
		}
		echo 
		"\n */\n",
		"class $classname extends Grammar {\n",
		"\n";
		
		// overload all non-null members
		// no way to auto-detect access level ??
		$protected = get_object_vars($this);
		unset( $protected['uindex'] );
		foreach( $protected as $var => $val ){
			if( is_null($val) ){
				continue;
			}
			echo 
			"/** @override \${$var} */\n",
			"protected \${$var} = ",var_export($val,1),";\n\n";
		}
		// end vars
		echo
		"}\n";
	}	
	
	
	
	
	
	
	
	
}