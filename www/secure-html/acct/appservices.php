<?php
require_once "appsrvlib.inc.php";

require_once "alib.inc.php";



list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in (); // does not return if not lo
$db = aconnect_db(); // connect to the right database
$info = make_acct_form_components($accid);
$desc = "MedCommons Purchase Services";
$title = "MedCommons Purchase Services";
$startpage='';
$top = make_acct_page_top ($info,$accid,$email,'',$desc,$title,$startpage,"showAppEvents.php");
$bottom = make_acct_page_bottom ($info);

$body = enumerate_appservices($accid);

$html = <<<XXX
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="MedCommons Purchase Services"/>
        <meta name="robots" content="all"/>
        <title>Purchase Additional Services for $accid</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>

        <style type="text/css" media="all"> @import "acctstyle.css";</style>
        <script type="text/javascript" src="instantedit.js"></script>
    
   </head>
    <body id="css-zen-garden"  >
    <div id="container">
    $top
  	$body
     </div>
     $bottom
XXX;
echo $html;
?>