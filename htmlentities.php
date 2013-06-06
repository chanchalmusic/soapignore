<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: akhtar
 * Date: 6/6/13
 * Time: 9:38 AM
 * To change this template use File | Settings | File Templates.
 */
$strText = 'Vor üÜ';
print_r("<b>original</b>: ".$strText);
echo "<br/>";

$strText = mb_convert_encoding($strText, 'HTML-ENTITIES', 'UTF-8');
print_r("<b>mb converted: </b>".$strText);
echo "<br/>";
$strText= htmlentities($strText);

print_r("<b>html entity</b>".$strText);
echo "<br/>";
$decodedStr = html_entity_decode($strText);
$teaser = mb_substr($decodedStr, 0, 6, 'UTF-8');

print_r("<b>decoded str: </b>".$decodedStr);
echo "<br />";

print_r("<b>teaser: </b>".$teaser);
echo "<br />";

//echo strlen($str);
//echo $str;
//echo "<br />";
///$encodedStr = mb_convert_encoding($str, 'HTML-ENTITIES', 'UTF-8');
//$encodedStr = htmlentities($str, ENT_COMPAT | 'ENT_HTML401', 'UTF-8');
//$encodedStr = htmlentities($str, ENT_COMPAT,'utf-8');
//echo $encodedStr;
//echo "<br />";
//$decodedStr = html_entity_decode($encodedStr,ENT_COMPAT, 'utf-8');
//echo $decodedStr;
//echo "<br />";
//echo strlen($decodedStr);
//echo substr($decodedStr, 0, 5);
//echo mb_substr($decodedStr, 0, 5, 'utf-8');
//echo  mb_strlen($decodedStr, 'utf-8');

//$strText = mb_convert_encoding($strText, 'HTML-ENTITIES', 'UTF-8');
//$strText= htmlentities($strText, ENT_COMPAT | 'ENT_HTML401', 'UTF-8');



?>
</body>
</html>