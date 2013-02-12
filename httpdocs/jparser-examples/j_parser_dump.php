<?php
/**
 * jParser example
 * Dumps out the parse tree in an XML format.
 * Parse tree is compact - redundant nodes are collapsed
 */
require '../jparser-libs/jparser.php';
 
/**
 * Get some source code from postdata or from example *.js file
 */
$source = isset($_POST['source']) ? $_POST['source'] : file_get_contents('simple.js');

/**
 * Get the full parse tree
 */
try {
	$Prog = JParser::parse_string( $source );
}
catch( ParseError $Ex ){
	$error = $Ex->getMessage()."\n----\n".$Ex->snip( $source );
}
catch( Exception $Ex ){
	$error = $Ex->getMessage();
}


/**
 * Show XML tree if check box ticked
 */
if( ! isset($error) && ! empty($_POST['xml']) ){
	header('Content-type: text/xml; charset=utf-8', true );
	echo '<?xml version="1.0" encoding="utf-8"?>';
	$Prog->dump( new JLex );
	exit(0);
}


/**
 * Output handler to encode XML tree as html
 */
function on_dump_output( $text ){
	return htmlentities($text,ENT_COMPAT,'UTF-8');
}



echo '<?xml version="1.0" encoding="UTF-8" ?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>jParser example :: j_parser_dump</title>
</head>
<body>


<form action="" method="post" enctype="application/x-www-form-urlencoded">
	<textarea name="source" rows="5" cols="40" wrap="virtual"><?php echo htmlentities($source,ENT_COMPAT,'UTF-8')?></textarea>
	<br />
	<input type="submit" value="Parse" />
	<label>
		<input type="checkbox" name="xml" value="1"<?php echo empty($_POST['xml'])?'':' checked="checked"'?> />
		XML
	</label>
</form>


<hr />


<?php if( isset($error)): ?>
	<pre style="font: 10pt monospace; color: #CC3300;"><?php
		echo htmlentities($error,ENT_COMPAT,'UTF-8');
	?></pre>
<?php else: ?>
	<pre><?php
		ob_start('on_dump_output');
		$Prog->dump( new JLex );
		ob_end_flush();
	?></pre>
<?php endif; ?>

</body>
</html>