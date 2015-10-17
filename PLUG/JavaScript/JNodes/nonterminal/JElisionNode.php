<?php

namespace PLUG\JavaScript\JNodes\nonterminal;

/**
 * File containing class JElisionNode
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JElisionNode.php,v 1.1 2009/03/22 12:51:08 twhitlock Exp $
 */

use PLUG\JavaScript\JNodes\JNodeBase;

/**
 * JavaScript node class for the J_ELISION symbol
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JElisionNode extends JNodeBase {

	/**
	 * Tests whether this node can be passed through when appending to given node
     * @param JNodeBase
	 * @return bool
	 */
	function is_transparent( JNodeBase $Parent ){
		return false;
		//return false && $this->length === 1;
	}


	/**
	 * @override
     */
	function __toString(){
		return parent::__toString();
	}


	/**
	 * @override
     */
	function evaluate(){
		return parent::evaluate();
	}


}
