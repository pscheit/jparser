<?php

namespace PLUG\JavaScript\JNodes\nonterminal;

/**
 * File containing class JPropNameNode
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 * @version $Id: JPropNameNode.php,v 1.1 2009/03/22 12:51:09 twhitlock Exp $
 */

use PLUG\JavaScript\JNodes\JNodeBase;

/**
 * JavaScript node class for the J_PROP_NAME symbol
 * @category PLUG
 * @package JavaScript
 * @subpackage JNodes
 */
class JPropNameNode extends JNodeBase {


	
	/**
	 * @override
	 * prop name in proplist may not be obfuscated as it is a property declaration
	 */
	function obfuscate( array &$names ){
		return;
	}

}
