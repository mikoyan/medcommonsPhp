<?php
/**
todir add completion
**/

$id = $_REQUEST['id']; //specifies the group

require_once "glib.inc.php";
list($accid,$fn,$ln,$email,$idp,$coookie) = confirm_logged_in (); // does not return if not logged on
$db = connect_db(); // connect to the right database
$id = $_REQUEST['id'];
confirm_admin_access($accid,$id); // does not return if this user is not a group admin
$info = make_group_form_components($id);
//main
 mysql_connect($GLOBALS['DB_Connection'],
    $GLOBALS['DB_User'],
    $GLOBALS['DB_Password']
    ) or die ("can not connect to mysql");
    $db = $GLOBALS['DB_Database'];
    mysql_select_db($db) or die ("can not connect to database $db");
    $timenow = time();
		$insert="INSERT INTO todir(xid,ctx,alias,contact,time,accid)
				VALUES('$xid','$ctx','$alias','$contact', '$timenow','$accid')"; // these come from args.inc
		mysql_query($insert) or die("can not insert into table todir - ");
// make a html header and insert the first round of content in the body
{ 
	//simple display, no ajax, one shot
$body = <<<XXX
<html>      <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="MedCommons Group Maintenance for Group $id"/>
        <meta name="robots" content="all"/>
        <title>MedCommons Group Maintenance</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "groups.css"; </style>
    </head>
<body style='margin: 0 20px 0 20px;' >
<table><tr><td><a href="index.html" ><img border="0" alt="MedCommons" 
                src="../images/mclogotiny.png" 
                title="ToDir Query Page" /></a>
                </td><td>ToDir Query Page <small><i>for internal use only</i>
                <a TARGET="_parent" href = 'todirquery.php?id=$id'>query</a>
&nbsp;<a TARGET="_parent" href = 'todiradd.php?id=$id'>add</a> &nbsp;
<a TARGET="_parent" href = 'todirdel.php?id=$id'>delete</a>
acct $accid $email</small></td>
</tr></table>
$info->header
<div id="content"> 
                       added to table
</div> 
</body></html>
XXX;

}// end of first time paint
echo $body;

?>