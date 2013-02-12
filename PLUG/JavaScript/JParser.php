<?php
/**
 * File containing class JParser
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @version $Id: JParser.php,v 1.1.2.1 2009/11/14 16:27:56 twhitlock Exp $
 */

 
import('PLUG.JavaScript.JParserBase'); 
//import('PLUG.JavaScript.JTokenizer'); // <- dependencies removed for distribution purposes
//import('PLUG.JavaScript.JLex'); 
import('PLUG.JavaScript.JGrammar'); 
import('PLUG.JavaScript.JParseTable'); 
import('PLUG.JavaScript.JNodes.JNodeBase'); 
import('PLUG.JavaScript.JNodes.nonterminal.*'); 
import('PLUG.JavaScript.JNodes.terminal.*'); 

 
/**
 * Full JavaScript parser
 * @category PLUG
 * @package JavaScript
 */
class JParser extends JParserBase {

	/** @override */
	protected $default_node_class = 'JNodeBase';
	
	/** @override */
	protected $node_classes = array (
		
		// Non-terminal node class registry
		//
		J_ADD_EXPR           => 'JAddExprNode',
		J_ARGS               => 'JArgsNode',
		J_ARG_LIST           => 'JArgListNode',
		J_ARRAY_LITERAL      => 'JArrayLiteralNode',
		J_ASSIGN_EXPR        => 'JAssignExprNode',
		J_ASSIGN_EXPR_NO_IN  => 'JAssignExprNode',
		J_ASSIGN_OP          => 'JAssignOpNode',
		J_BIT_AND_EXPR       => 'JBitAndExprNode',
		J_BIT_AND_EXPR_NO_IN => 'JBitAndExprNode',
		J_BIT_OR_EXPR        => 'JBitOrExprNode',
		J_BIT_OR_EXPR_NO_IN  => 'JBitOrExprNode',
		J_BIT_XOR_EXPR       => 'JBitXorExprNode',
		J_BIT_XOR_EXPR_NO_IN => 'JBitXorExprNode',
		J_BLOCK              => 'JBlockNode',
		J_BREAK_STATEMENT    => 'JBreakStatementNode',
		J_CALL_EXPR          => 'JCallExprNode',
		J_CASE_BLOCK         => 'JCaseBlockNode',
		J_CASE_CLAUSE        => 'JCaseClauseNode',
		J_CASE_CLAUSES       => 'JCaseClausesNode',
		J_CASE_DEFAULT       => 'JCaseDefaultNode',
		J_CATCH_CLAUSE       => 'JCatchClauseNode',
		J_COND_EXPR          => 'JCondExprNode',
		J_COND_EXPR_NO_IN    => 'JCondExprNode',
		J_CONT_STATEMENT     => 'JContStatementNode',
		J_ELEMENT            => 'JElementNode',
		J_ELEMENTS           => 'JElementsNode',
		J_ELEMENT_LIST       => 'JElementListNode',
		J_ELISION            => 'JElisionNode',
		J_EMPTY_STATEMENT    => 'JEmptyStatementNode',
		J_EQ_EXPR            => 'JEqExprNode',
		J_EQ_EXPR_NO_IN      => 'JEqExprNode',
		J_EXPR               => 'JExprNode',
		J_EXPR_NO_IN         => 'JExprNode',
		J_EXPR_STATEMENT     => 'JExprStatementNode',
		J_FINALLY_CLAUSE     => 'JFinallyClauseNode',
		J_FUNC_BODY          => 'JFuncBodyNode',
		J_FUNC_DECL          => 'JFuncDeclNode',
		J_FUNC_EXPR          => 'JFuncExprNode',
		J_IF_STATEMENT       => 'JIfStatementNode',
		J_INITIALIZER        => 'JInitializerNode',
		J_INITIALIZER_NO_IN  => 'JInitializerNode',
		J_ITER_STATEMENT     => 'JIterStatementNode',
		J_LABELLED_STATEMENT => 'JLabelledStatementNode',
		J_LHS_EXPR           => 'JLhsExprNode',
		J_LOG_AND_EXPR       => 'JLogAndExprNode',
		J_LOG_AND_EXPR_NO_IN => 'JLogAndExprNode',
		J_LOG_OR_EXPR        => 'JLogOrExprNode',
		J_LOG_OR_EXPR_NO_IN  => 'JLogOrExprNode',
		J_MEMBER_EXPR        => 'JMemberExprNode',
		J_MULT_EXPR          => 'JMultExprNode',
		J_NEW_EXPR           => 'JNewExprNode',
		J_OBJECT_LITERAL     => 'JObjectLiteralNode',
		J_PARAM_LIST         => 'JParamListNode',
		J_POSTFIX_EXPR       => 'JPostfixExprNode',
		J_PRIMARY_EXPR       => 'JPrimaryExprNode',
		J_PROGRAM            => 'JProgramNode',
		J_PROP_LIST          => 'JPropListNode',
		J_PROP_NAME          => 'JPropNameNode',
		J_REL_EXPR           => 'JRelExprNode',
		J_REL_EXPR_NO_IN     => 'JRelExprNode',
		J_RETURN_STATEMENT   => 'JReturnStatementNode',
		J_SHIFT_EXPR         => 'JShiftExprNode',
		J_STATEMENT          => 'JStatementNode',
		J_STATEMENT_LIST     => 'JStatementListNode',
		J_SWITCH_STATEMENT   => 'JSwitchStatementNode',
		J_THROW_STATEMENT    => 'JThrowStatementNode',
		J_TRY_STATEMENT      => 'JTryStatementNode',
		J_UNARY_EXPR         => 'JUnaryExprNode',
		J_VAR_DECL           => 'JVarDeclNode',
		J_VAR_DECL_LIST      => 'JVarDeclListNode',
		J_VAR_DECL_LIST_NO_IN => 'JVarDeclListNode',
		J_VAR_DECL_NO_IN     => 'JVarDeclNode',
		J_VAR_STATEMENT      => 'JVarStatementNode',
		J_WITH_STATEMENT     => 'JWithStatementNode',
		
		// Terminal node class registry
		//
		//J_ABSTRACT        => 'JAbstractNode',
		//J_BOOLEAN         => 'JBooleanNode',
		//J_BREAK           => 'JBreakNode',
		//J_BYTE            => 'JByteNode',
		//J_CASE            => 'JCaseNode',
		//J_CATCH           => 'JCatchNode',
		//J_CHAR            => 'JCharNode',
		//J_CLASS           => 'JClassNode',
		//J_COMMENT         => 'JCommentNode',
		//J_CONST           => 'JConstNode',
		//J_CONTINUE        => 'JContinueNode',
		//J_DEBUGGER        => 'JDebuggerNode',
		//J_DEFAULT         => 'JDefaultNode',
		//J_DELETE          => 'JDeleteNode',
		//J_DO              => 'JDoNode',
		//J_DOUBLE          => 'JDoubleNode',
		//J_ELSE            => 'JElseNode',
		//J_ENUM            => 'JEnumNode',
		//J_EXPORT          => 'JExportNode',
		//J_EXTENDS         => 'JExtendsNode',
		//J_FALSE           => 'JFalseNode',
		//J_FINAL           => 'JFinalNode',
		//J_FINALLY         => 'JFinallyNode',
		//J_FLOAT           => 'JFloatNode',
		//J_FOR             => 'JForNode',
		//J_FUNCTION        => 'JFunctionNode',
		//J_GOTO            => 'JGotoNode',
		J_IDENTIFIER      => 'JIdentifierNode',
		//J_IF              => 'JIfNode',
		//J_IMPLEMENTS      => 'JImplementsNode',
		//J_IMPORT          => 'JImportNode',
		//J_IN              => 'JInNode',
		//J_INSTANCEOF      => 'JInstanceofNode',
		//J_INT             => 'JIntNode',
		//J_INTERFACE       => 'JInterfaceNode',
		//J_LINE_TERMINATOR => 'JLineTerminatorNode',
		//J_LONG            => 'JLongNode',
		//J_NATIVE          => 'JNativeNode',
		//J_NEW             => 'JNewNode',
		//J_NULL            => 'JNullNode',
		//J_NUMERIC_LITERAL => 'JNumericLiteralNode',
		//J_PACKAGE         => 'JPackageNode',
		//J_PRIVATE         => 'JPrivateNode',
		//J_PROTECTED       => 'JProtectedNode',
		//J_PUBLIC          => 'JPublicNode',
		//J_REGEX           => 'JRegexNode',
		//J_RETURN          => 'JReturnNode',
		//J_SHORT           => 'JShortNode',
		//J_STATIC          => 'JStaticNode',
		//J_STRING_LITERAL  => 'JStringLiteralNode',
		//J_SUPER           => 'JSuperNode',
		//J_SWITCH          => 'JSwitchNode',
		//J_SYNCHRONIZED    => 'JSynchronizedNode',
		//J_THIS            => 'JThisNode',
		//J_THROW           => 'JThrowNode',
		//J_THROWS          => 'JThrowsNode',
		//J_TRANSIENT       => 'JTransientNode',
		//J_TRUE            => 'JTrueNode',
		//J_TRY             => 'JTryNode',
		//J_TYPEOF          => 'JTypeofNode',
		//J_VAR             => 'JVarNode',
		//J_VOID            => 'JVoidNode',
		//J_VOLATILE        => 'JVolatileNode',
		//J_WHILE           => 'JWhileNode',
		//J_WHITESPACE      => 'JWhitespaceNode',
		//J_WITH            => 'JWithNode',		
	);
	//*/
		
	
	/**
	 * Constructor
	 */
	function __construct(){
		// super-construct with Lex and Grammar
		parent::__construct( new JLex, new JGrammar );
		// set pre-compiled parse table
		$this->Table = new JParseTable;
	}
	
	
	
	/**
	 * @override
	 */
	static function parse_string( $src, $unicode = true, $parser = __CLASS__, $lexer = 'JTokenizer' ){
		return parent::parse_string( $src, $unicode, $parser, $lexer );
	}
	
}
	
 
 
