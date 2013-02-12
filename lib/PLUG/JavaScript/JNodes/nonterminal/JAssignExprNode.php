<?php
/**
 * File containing class JAssignExprNode
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JAssignExprNode.php,v 1.1 2009/03/22 12:51:08 twhitlock Exp $
 */
 
 
/**
 * JavaScript node class for the J_ASSIGN_EXPR symbol
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JAssignExprNode extends JNodeBase {

	/**
	 * Tests whether this node can be passed through when appending to given node
     * @param JNodeBase
	 * @return bool
	 */
	function is_transparent( JNodeBase $Parent ){
		return true && $this->length === 1;
	}


	
		

}
