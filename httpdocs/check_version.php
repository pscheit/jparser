<?php echo'<?xml version="1.0" encoding="UTF-8" ?>
'?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Checking PHP version</title>
</head>
<body>
<pre>
<strong>PHP_VERSION = <?php echo PHP_VERSION?></strong>
<?php
if( version_compare('5.2',PHP_VERSION) === 1 ){
	echo 'Your version of PHP is below 5.2 - jParser will probably not run properly';
}
else {
	echo 'Your version of PHP is at least 5.2 - well done you!';
}
if ('win' === strtolower(substr(PHP_OS, 0, 3))) {
	echo "\nYour are running <strong>Windows</strong> - **jParser has not been tested on Windows**";
}
?>
</pre>
</body>
</html>