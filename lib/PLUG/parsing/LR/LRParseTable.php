<?php
/**
 * File containing class LRParseTable
 * @author Tim Whitlock
 * @category PLUG
 * @package parsing
 * @subpackage LR
 * @version $Id: LRParseTable.php,v 1.1 2009/03/22 12:55:30 twhitlock Exp $
 */


/**
 * LR Parse Table base class
 * @category PLUG
 * @package parsing
 * @subpackage LR
 */
abstract class LRParseTable {
 
	/**
	 * Actual table data
	 * @var array
	 */	 
 	protected $table;
 
	/**
	 * Constructor
	 * @param array raw table data
	 */ 
	function __construct( array $table = null ){
		if( ! is_null( $table ) ){
			$this->table = $table;
		}
	}
 
 
	/**
	 * @return array raw table data
	 */ 
	function export(){
		return $this->table;
	}
	
	
	
	
	/**
	 * Lookup action by state and lookahead symbol
	 * @param int odd-numbered state id
	 * @param mixed look ahead symbol
	 * @return int either an even-numbered rule id, or an odd-numbered state id, or null
	 */	
	function lookup( $state, $la ){
		if( ! isset($this->table[$state][$la]) ){
			return null;
		}	
		else {
			return $this->table[$state][$la];
		}
	}
	
	
	
	/**
	 * Lookup which terminal symbols are permitted in a given state
	 */	
	function permitted( $state, Grammar $Grammar ){
		if( ! isset($this->table[$state]) ){
			return array();
		}
		$all = array_keys( $this->table[$state] );
		//return $all;
		$a = array();	
		foreach( $all as $t ){
			if( $Grammar->is_terminal($t) ){
				$a[] = $t;
			}
		}
		return $a;
	}
	
	
	
	/**
	 * Debugging function, makes table legible
	 * @param Lex
	 * @param Grammar
	 * @param int optionally dump specific row (state)
	 * @return void
	 */	
	function dump( Lex $Lex, Grammar $Grammar, $state = null ){
		$table = array();
		$heads = array( '' => 0 );
		// translate cell data and get other meta data
		foreach( $this->table as $i => $row ){
			if( ! is_null($state) && $i !== $state ){
				continue;
			}
			$table[$i] = array();
			// create row header
			$table[$i][''] = "#$i";
			$heads[''] = max( $heads[''], strlen($table[$i]['']) );
			// iterate over cols in this row
			foreach( $row as $sym => $entry ){
				if( is_null($sym) ){
					$sym = 'null';
				}
				else{
					$sym = $Lex->name($sym);
				}
				// rules are event, states are odd
				if( $entry & 1 ){
					$str = " #$entry ";
				}
				else {
					list( $nt, $rhs ) = $Grammar->get_rule( $entry );
					$str = ' '.$Lex->name($nt).' -> ';
					foreach( $rhs as $t ){
						$str .= $Lex->name($t).' ';
					}
				}
				// insert cell
				$table[$i][$sym] = $str;
				// collect known column header with max cell width in column
				if( !isset($heads[$sym]) ){
					$heads[$sym] = strlen($sym);
				}
				$heads[$sym] = max( $heads[$sym], strlen($str) );
			}
		}
		// print all headers
		$a = array();
		$b = array();
		foreach( $heads as $sym => $len ){
			$b[] = str_repeat( '-', $len );
			$a[] = str_pad( $sym, $len, ' ', STR_PAD_BOTH );
		}
		echo '+', implode('+', $b ), "+\n";
		echo '|', implode('|', $a ), "|\n";
		foreach( $table as $i => $row ){
			$c = array();
			foreach( $heads as $sym => $len ){
				if( isset($table[$i][$sym]) ){
					$c[] = str_pad( $row[$sym], $len, ' ', STR_PAD_BOTH );
				}
				else {
					$c[] = str_repeat(' ', $len );
				}
			}
			echo '+', implode('+', $b ), "+\n";
			echo '|', implode('|', $c ), "|\n";
		}
		echo '+', implode('+', $b ), "+\n";
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
		"import('PLUG.parsing.LR.LRParseTable');\n",
		"\n",
		"/**\n * Auto-generated LRParseTable subclass";
		// unset page level doc block tags we don't want against the class
		unset (
			$commentData['author'], $commentData['version']
		);
		foreach( $commentData as $tag => $value ){
			echo "\n * @$tag $value";
		}
		echo 
		"\n */\n",
		"class $classname extends LRParseTable {\n",
		"\n",
		"/** @var array */\n",
		"protected \$table = ",var_export($this->table,1),";\n\n",
		"}\n";
	}	
	

 
 
 
 }
 
 