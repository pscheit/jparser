<?php
/**
 * File containing class JArgsNode
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JArgsNode.php,v 1.1 2009/03/22 12:51:08 twhitlock Exp $
 */
 
 
/**
 * JavaScript node class for the J_ARGS symbol
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JArgsNode extends JNodeBase {

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
