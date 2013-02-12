<?php
/**
 * File containing class JWithStatementNode
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JWithStatementNode.php,v 1.1 2009/03/22 12:51:09 twhitlock Exp $
 */
 
 
/**
 * JavaScript node class for the J_WITH_STATEMENT symbol
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JWithStatementNode extends JNodeBase {

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
