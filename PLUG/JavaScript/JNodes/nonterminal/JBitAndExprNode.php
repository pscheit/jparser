<?php

namespace PLUG\JavaScript\JNodes\nonterminal;

/**
 * File containing class JBitAndExprNode
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JBitAndExprNode.php,v 1.1 2009/03/22 12:51:08 twhitlock Exp $
 */

use PLUG\JavaScript\JNodes\JNodeBase;

/**
 * JavaScript node class for the J_BIT_AND_EXPR symbol
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JBitAndExprNode extends JNodeBase {

	/**
	 * Tests whether this node can be passed through when appending to given node
     * @param JNodeBase
	 * @return bool
	 */
	function is_transparent( JNodeBase $Parent ){
		return true && $this->length === 1;
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
