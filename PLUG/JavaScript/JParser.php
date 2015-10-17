<?php

namespace PLUG\JavaScript;

/**
 * File containing class JParser
 * @author Tim Whitlock
 * @category PLUG
 * @package JavaScript
 * @version $Id: JParser.php,v 1.1.2.1 2009/11/14 16:27:56 twhitlock Exp $
 */

 
use PLUG\JavaScript\JParserBase; 
use PLUG\JavaScript\JTokenizer;
//use PLUG\JavaScript\JLex; 
use PLUG\JavaScript\JGrammar; 
use PLUG\JavaScript\JParseTable; 
use PLUG\JavaScript\JNodes\JNodeBase;

 
/**
 * Full JavaScript parser
 * @category PLUG
 * @package JavaScript
 */
class JParser extends JParserBase {

	/** @override */
	protected $default_node_class = JNodeBase::class;
	
	/** @override */
	protected $node_classes = array (
		
		// Non-terminal node class registry
		//
		J_ADD_EXPR           => \PLUG\JavaScript\JNodes\nonterminal\JAddExprNode::class,
		J_ARGS               => \PLUG\JavaScript\JNodes\nonterminal\JArgsNode::class,
		J_ARG_LIST           => \PLUG\JavaScript\JNodes\nonterminal\JArgListNode::class,
		J_ARRAY_LITERAL      => \PLUG\JavaScript\JNodes\nonterminal\JArrayLiteralNode::class,
		J_ASSIGN_EXPR        => \PLUG\JavaScript\JNodes\nonterminal\JAssignExprNode::class,
		J_ASSIGN_EXPR_NO_IN  => \PLUG\JavaScript\JNodes\nonterminal\JAssignExprNode::class,
		J_ASSIGN_OP          => \PLUG\JavaScript\JNodes\nonterminal\JAssignOpNode::class,
		J_BIT_AND_EXPR       => \PLUG\JavaScript\JNodes\nonterminal\JBitAndExprNode::class,
		J_BIT_AND_EXPR_NO_IN => \PLUG\JavaScript\JNodes\nonterminal\JBitAndExprNode::class,
		J_BIT_OR_EXPR        => \PLUG\JavaScript\JNodes\nonterminal\JBitOrExprNode::class,
		J_BIT_OR_EXPR_NO_IN  => \PLUG\JavaScript\JNodes\nonterminal\JBitOrExprNode::class,
		J_BIT_XOR_EXPR       => \PLUG\JavaScript\JNodes\nonterminal\JBitXorExprNode::class,
		J_BIT_XOR_EXPR_NO_IN => \PLUG\JavaScript\JNodes\nonterminal\JBitXorExprNode::class,
		J_BLOCK              => \PLUG\JavaScript\JNodes\nonterminal\JBlockNode::class,
		J_BREAK_STATEMENT    => \PLUG\JavaScript\JNodes\nonterminal\JBreakStatementNode::class,
		J_CALL_EXPR          => \PLUG\JavaScript\JNodes\nonterminal\JCallExprNode::class,
		J_CASE_BLOCK         => \PLUG\JavaScript\JNodes\nonterminal\JCaseBlockNode::class,
		J_CASE_CLAUSE        => \PLUG\JavaScript\JNodes\nonterminal\JCaseClauseNode::class,
		J_CASE_CLAUSES       => \PLUG\JavaScript\JNodes\nonterminal\JCaseClausesNode::class,
		J_CASE_DEFAULT       => \PLUG\JavaScript\JNodes\nonterminal\JCaseDefaultNode::class,
		J_CATCH_CLAUSE       => \PLUG\JavaScript\JNodes\nonterminal\JCatchClauseNode::class,
		J_COND_EXPR          => \PLUG\JavaScript\JNodes\nonterminal\JCondExprNode::class,
		J_COND_EXPR_NO_IN    => \PLUG\JavaScript\JNodes\nonterminal\JCondExprNode::class,
		J_CONT_STATEMENT     => \PLUG\JavaScript\JNodes\nonterminal\JContStatementNode::class,
		J_ELEMENT            => \PLUG\JavaScript\JNodes\nonterminal\JElementNode::class,
		J_ELEMENTS           => \PLUG\JavaScript\JNodes\nonterminal\JElementsNode::class,
		J_ELEMENT_LIST       => \PLUG\JavaScript\JNodes\nonterminal\JElementListNode::class,
		J_ELISION            => \PLUG\JavaScript\JNodes\nonterminal\JElisionNode::class,
		J_EMPTY_STATEMENT    => \PLUG\JavaScript\JNodes\nonterminal\JEmptyStatementNode::class,
		J_EQ_EXPR            => \PLUG\JavaScript\JNodes\nonterminal\JEqExprNode::class,
		J_EQ_EXPR_NO_IN      => \PLUG\JavaScript\JNodes\nonterminal\JEqExprNode::class,
		J_EXPR               => \PLUG\JavaScript\JNodes\nonterminal\JExprNode::class,
		J_EXPR_NO_IN         => \PLUG\JavaScript\JNodes\nonterminal\JExprNode::class,
		J_EXPR_STATEMENT     => \PLUG\JavaScript\JNodes\nonterminal\JExprStatementNode::class,
		J_FINALLY_CLAUSE     => \PLUG\JavaScript\JNodes\nonterminal\JFinallyClauseNode::class,
		J_FUNC_BODY          => \PLUG\JavaScript\JNodes\nonterminal\JFuncBodyNode::class,
		J_FUNC_DECL          => \PLUG\JavaScript\JNodes\nonterminal\JFuncDeclNode::class,
		J_FUNC_EXPR          => \PLUG\JavaScript\JNodes\nonterminal\JFuncExprNode::class,
		J_IF_STATEMENT       => \PLUG\JavaScript\JNodes\nonterminal\JIfStatementNode::class,
		J_INITIALIZER        => \PLUG\JavaScript\JNodes\nonterminal\JInitializerNode::class,
		J_INITIALIZER_NO_IN  => \PLUG\JavaScript\JNodes\nonterminal\JInitializerNode::class,
		J_ITER_STATEMENT     => \PLUG\JavaScript\JNodes\nonterminal\JIterStatementNode::class,
		J_LABELLED_STATEMENT => \PLUG\JavaScript\JNodes\nonterminal\JLabelledStatementNode::class,
		J_LHS_EXPR           => \PLUG\JavaScript\JNodes\nonterminal\JLhsExprNode::class,
		J_LOG_AND_EXPR       => \PLUG\JavaScript\JNodes\nonterminal\JLogAndExprNode::class,
		J_LOG_AND_EXPR_NO_IN => \PLUG\JavaScript\JNodes\nonterminal\JLogAndExprNode::class,
		J_LOG_OR_EXPR        => \PLUG\JavaScript\JNodes\nonterminal\JLogOrExprNode::class,
		J_LOG_OR_EXPR_NO_IN  => \PLUG\JavaScript\JNodes\nonterminal\JLogOrExprNode::class,
		J_MEMBER_EXPR        => \PLUG\JavaScript\JNodes\nonterminal\JMemberExprNode::class,
		J_MULT_EXPR          => \PLUG\JavaScript\JNodes\nonterminal\JMultExprNode::class,
		J_NEW_EXPR           => \PLUG\JavaScript\JNodes\nonterminal\JNewExprNode::class,
		J_OBJECT_LITERAL     => \PLUG\JavaScript\JNodes\nonterminal\JObjectLiteralNode::class,
		J_PARAM_LIST         => \PLUG\JavaScript\JNodes\nonterminal\JParamListNode::class,
		J_POSTFIX_EXPR       => \PLUG\JavaScript\JNodes\nonterminal\JPostfixExprNode::class,
		J_PRIMARY_EXPR       => \PLUG\JavaScript\JNodes\nonterminal\JPrimaryExprNode::class,
		J_PROGRAM            => \PLUG\JavaScript\JNodes\nonterminal\JProgramNode::class,
		J_PROP_LIST          => \PLUG\JavaScript\JNodes\nonterminal\JPropListNode::class,
		J_PROP_NAME          => \PLUG\JavaScript\JNodes\nonterminal\JPropNameNode::class,
		J_REL_EXPR           => \PLUG\JavaScript\JNodes\nonterminal\JRelExprNode::class,
		J_REL_EXPR_NO_IN     => \PLUG\JavaScript\JNodes\nonterminal\JRelExprNode::class,
		J_RETURN_STATEMENT   => \PLUG\JavaScript\JNodes\nonterminal\JReturnStatementNode::class,
		J_SHIFT_EXPR         => \PLUG\JavaScript\JNodes\nonterminal\JShiftExprNode::class,
		J_STATEMENT          => \PLUG\JavaScript\JNodes\nonterminal\JStatementNode::class,
		J_STATEMENT_LIST     => \PLUG\JavaScript\JNodes\nonterminal\JStatementListNode::class,
		J_SWITCH_STATEMENT   => \PLUG\JavaScript\JNodes\nonterminal\JSwitchStatementNode::class,
		J_THROW_STATEMENT    => \PLUG\JavaScript\JNodes\nonterminal\JThrowStatementNode::class,
		J_TRY_STATEMENT      => \PLUG\JavaScript\JNodes\nonterminal\JTryStatementNode::class,
		J_UNARY_EXPR         => \PLUG\JavaScript\JNodes\nonterminal\JUnaryExprNode::class,
		J_VAR_DECL           => \PLUG\JavaScript\JNodes\nonterminal\JVarDeclNode::class,
		J_VAR_DECL_LIST      => \PLUG\JavaScript\JNodes\nonterminal\JVarDeclListNode::class,
		J_VAR_DECL_LIST_NO_IN => \PLUG\JavaScript\JNodes\nonterminal\JVarDeclListNode::class,
		J_VAR_DECL_NO_IN     => \PLUG\JavaScript\JNodes\nonterminal\JVarDeclNode::class,
		J_VAR_STATEMENT      => \PLUG\JavaScript\JNodes\nonterminal\JVarStatementNode::class,
		J_WITH_STATEMENT     => \PLUG\JavaScript\JNodes\nonterminal\JWithStatementNode::class,
		
		// Terminal node class registry
		//
		//J_ABSTRACT        => \PLUG\JavaScript\JNodes\terminal\JAbstractNode::class,
		//J_BOOLEAN         => \PLUG\JavaScript\JNodes\terminal\JBooleanNode::class,
		//J_BREAK           => \PLUG\JavaScript\JNodes\terminal\JBreakNode::class,
		//J_BYTE            => \PLUG\JavaScript\JNodes\terminal\JByteNode::class,
		//J_CASE            => \PLUG\JavaScript\JNodes\terminal\JCaseNode::class,
		//J_CATCH           => \PLUG\JavaScript\JNodes\terminal\JCatchNode::class,
		//J_CHAR            => \PLUG\JavaScript\JNodes\terminal\JCharNode::class,
		//J_CLASS           => \PLUG\JavaScript\JNodes\terminal\JClassNode::class,
		//J_COMMENT         => \PLUG\JavaScript\JNodes\terminal\JCommentNode::class,
		//J_CONST           => \PLUG\JavaScript\JNodes\terminal\JConstNode::class,
		//J_CONTINUE        => \PLUG\JavaScript\JNodes\terminal\JContinueNode::class,
		//J_DEBUGGER        => \PLUG\JavaScript\JNodes\terminal\JDebuggerNode::class,
		//J_DEFAULT         => \PLUG\JavaScript\JNodes\terminal\JDefaultNode::class,
		//J_DELETE          => \PLUG\JavaScript\JNodes\terminal\JDeleteNode::class,
		//J_DO              => \PLUG\JavaScript\JNodes\terminal\JDoNode::class,
		//J_DOUBLE          => \PLUG\JavaScript\JNodes\terminal\JDoubleNode::class,
		//J_ELSE            => \PLUG\JavaScript\JNodes\terminal\JElseNode::class,
		//J_ENUM            => \PLUG\JavaScript\JNodes\terminal\JEnumNode::class,
		//J_EXPORT          => \PLUG\JavaScript\JNodes\terminal\JExportNode::class,
		//J_EXTENDS         => \PLUG\JavaScript\JNodes\terminal\JExtendsNode::class,
		//J_FALSE           => \PLUG\JavaScript\JNodes\terminal\JFalseNode::class,
		//J_FINAL           => \PLUG\JavaScript\JNodes\terminal\JFinalNode::class,
		//J_FINALLY         => \PLUG\JavaScript\JNodes\terminal\JFinallyNode::class,
		//J_FLOAT           => \PLUG\JavaScript\JNodes\terminal\JFloatNode::class,
		//J_FOR             => \PLUG\JavaScript\JNodes\terminal\JForNode::class,
		//J_FUNCTION        => \PLUG\JavaScript\JNodes\terminal\JFunctionNode::class,
		//J_GOTO            => \PLUG\JavaScript\JNodes\terminal\JGotoNode::class,
		J_IDENTIFIER      => \PLUG\JavaScript\JNodes\terminal\JIdentifierNode::class,
		//J_IF              => \PLUG\JavaScript\JNodes\terminal\JIfNode::class,
		//J_IMPLEMENTS      => \PLUG\JavaScript\JNodes\terminal\JImplementsNode::class,
		//J_IMPORT          => \PLUG\JavaScript\JNodes\terminal\JImportNode::class,
		//J_IN              => \PLUG\JavaScript\JNodes\terminal\JInNode::class,
		//J_INSTANCEOF      => \PLUG\JavaScript\JNodes\terminal\JInstanceofNode::class,
		//J_INT             => \PLUG\JavaScript\JNodes\terminal\JIntNode::class,
		//J_INTERFACE       => \PLUG\JavaScript\JNodes\terminal\JInterfaceNode::class,
		//J_LINE_TERMINATOR => \PLUG\JavaScript\JNodes\terminal\JLineTerminatorNode::class,
		//J_LONG            => \PLUG\JavaScript\JNodes\terminal\JLongNode::class,
		//J_NATIVE          => \PLUG\JavaScript\JNodes\terminal\JNativeNode::class,
		//J_NEW             => \PLUG\JavaScript\JNodes\terminal\JNewNode::class,
		//J_NULL            => \PLUG\JavaScript\JNodes\terminal\JNullNode::class,
		//J_NUMERIC_LITERAL => \PLUG\JavaScript\JNodes\terminal\JNumericLiteralNode::class,
		//J_PACKAGE         => \PLUG\JavaScript\JNodes\terminal\JPackageNode::class,
		//J_PRIVATE         => \PLUG\JavaScript\JNodes\terminal\JPrivateNode::class,
		//J_PROTECTED       => \PLUG\JavaScript\JNodes\terminal\JProtectedNode::class,
		//J_PUBLIC          => \PLUG\JavaScript\JNodes\terminal\JPublicNode::class,
		//J_REGEX           => \PLUG\JavaScript\JNodes\terminal\JRegexNode::class,
		//J_RETURN          => \PLUG\JavaScript\JNodes\terminal\JReturnNode::class,
		//J_SHORT           => \PLUG\JavaScript\JNodes\terminal\JShortNode::class,
		//J_STATIC          => \PLUG\JavaScript\JNodes\terminal\JStaticNode::class,
		//J_STRING_LITERAL  => \PLUG\JavaScript\JNodes\terminal\JStringLiteralNode::class,
		//J_SUPER           => \PLUG\JavaScript\JNodes\terminal\JSuperNode::class,
		//J_SWITCH          => \PLUG\JavaScript\JNodes\terminal\JSwitchNode::class,
		//J_SYNCHRONIZED    => \PLUG\JavaScript\JNodes\terminal\JSynchronizedNode::class,
		//J_THIS            => \PLUG\JavaScript\JNodes\terminal\JThisNode::class,
		//J_THROW           => \PLUG\JavaScript\JNodes\terminal\JThrowNode::class,
		//J_THROWS          => \PLUG\JavaScript\JNodes\terminal\JThrowsNode::class,
		//J_TRANSIENT       => \PLUG\JavaScript\JNodes\terminal\JTransientNode::class,
		//J_TRUE            => \PLUG\JavaScript\JNodes\terminal\JTrueNode::class,
		//J_TRY             => \PLUG\JavaScript\JNodes\terminal\JTryNode::class,
		//J_TYPEOF          => \PLUG\JavaScript\JNodes\terminal\JTypeofNode::class,
		//J_VAR             => \PLUG\JavaScript\JNodes\terminal\JVarNode::class,
		//J_VOID            => \PLUG\JavaScript\JNodes\terminal\JVoidNode::class,
		//J_VOLATILE        => \PLUG\JavaScript\JNodes\terminal\JVolatileNode::class,
		//J_WHILE           => \PLUG\JavaScript\JNodes\terminal\JWhileNode::class,
		//J_WHITESPACE      => \PLUG\JavaScript\JNodes\terminal\JWhitespaceNode::class,
		//J_WITH            => \PLUG\JavaScript\JNodes\terminal\JWithNode::class,		
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
	static function parse_string( $src, $unicode = true, $parser = __CLASS__, $lexer = JTokenizer::class ){
		return parent::parse_string( $src, $unicode, $parser, $lexer );
	}
	
}
	
 
 
