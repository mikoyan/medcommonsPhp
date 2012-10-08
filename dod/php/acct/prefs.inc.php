<?php
require_once "showstart.inc.php";
function make_select($name, $dulist,$selected)
{
	$count = count($dulist);
	$x="<select name='$name'>";
	for ($i=0; $i<$count; $i++)
	{
		$t = $dulist[$i];
		{
			$sel = ($selected==$t[0])?' selected="selected" ':'';
			$x.="<option value='$t[0]' $sel >$t[1]\r\n";
		}
	}
	$x.="</select>";
	return $x;
}
function get_afflist($accid)
{    // just start with practices I have identitified
	$ret[] = array(-1,"--none--");
	$query = "SELECT * from affiliates ";

	$result = mysql_query ($query) or die("can not query table groupmembers - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	$odd = false;  $first = true;
	if ($rowcount != 0) {
		while (true) {
			$a = mysql_fetch_object($result);
			if ($a===false) break;
			$ret[] = array($a->affiliateid,$a->affiliatename);
		}
	}
	return $ret;
}
function get_personas($accid)
{    // just start with practices I have identitified
	$ret[] = array(-1,"--account--");
	$query = "SELECT * from personas where '$accid'= accid ";

	$result = mysql_query ($query) or die("can not query table personas - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	$odd = false;  $first = true;
	if ($rowcount != 0) {
		while (true) {
			$a = mysql_fetch_object($result);
			if ($a===false) break;
			$ret[] = array($a->persona,$a->persona);
		}
	}
	return $ret;
}
function get_chargelist($accid)
{    $ret = array();
$ret[] = array("bronze","bronze");
$ret[] = array("silver","silver");
$ret[] = array("gold","gold");
$ret[] = array("free","free -- for testing only");
return $ret;
}
function get_faxlist($accid)
{    $ret = array();
$ret[] = array("none","I don't want to use faxes with my MedCommons Account");
$ret[] = array("incoming","I want to recieve incoming barcode faxes at $.25/page");
$ret[] = array("outgoing","I want to send outgoing faxes to recipients not on MedCommons at $.25/page");
$ret[] = array("both","I'll pay $.25/page for both incoming and outgoing faxes");
return $ret;
}


function get_startpagelist($accid)
{    
$ret = array();
$ret[] = array("ccr|rls|adm|topics","ccr|rls|adm|topics");
$ret[] = array("ccr|rls","ccr|rls");
$ret[] = array("ccr","ccr");
$ret[] = array("rls","rls");
$ret[] = array("adm","adm");
$ret[] = array("topics","topics");
return $ret;
}
function get_picslayoutlist($accid)
{
	$ret = array();
	$ret[] = array("SA","Subject Left Admin Right");
	$ret[] = array("AS","Admin Left  Subject Right");
	$ret[] = array("SS","Subject Left Subject Right");
	$ret[] = array("SX","Subject Left");
	$ret[] = array("XS","Subject Right");
	$ret[] = array("XX","no layout");
	return $ret;
}

function set_prefs($accid,$valid){
	$query = "SELECT affiliationgroupid, chargeclass,photoUrl,stylesheetUrl,picslayout,rolehack,persona from users where (mcid='$accid')";
	$result = mysql_query($query) or die("Can not query users - $query ".mysql_error());
	$row = mysql_fetch_array($result);
	$myaffiliation=$row[0]; $mychargeclass=$row[1]; $myphotoUrl=$row[2]; $mystylesheetUrl = $row[3];
	$mypickslayoutclass=$row[4]; $mystartpageclass=$row[5];$mypersona = $row[6];
	$afflist = get_afflist($accid); //returns list of affiliateids and tags I can select
	$chargelist = get_chargelist($accid); //returns list of chargeclasses and tags
	$faxlist = get_faxlist($accid);
	//$personalist = get_personas($accid);
	//	$personaselect = make_select('persona',$personalist,$mypersona);
	//<tr><td>Persona:</td><td>$personaselect<input type='submit' value='$updatelabel' name='personasubmit' />&nbsp;<a href='modpersona.php'><small>customize personas</small></a></td></tr>

	$startpagelist = get_startpagelist($accid);
	$picslayoutlist = get_picslayoutlist($accid);
	$affselect = make_select('affiliationgroupid',$afflist,$myaffiliation); //build the select statement
	$chargeselect = make_select('chargeclass',$chargelist,$mychargeclass); // likewise
	$faxselect = make_select('fax',$faxlist,''); // likewise
$startpageselect = make_select('rolehack',$startpagelist,$mystartpageclass); // likewise
	$picslayoutselect = make_select('picslayout',$picslayoutlist,$mypickslayoutclass); //
	$showstart = showStart();
	$updatelabel='go';
	$div = <<<XXX
<div id='prefs'>
<h4>Login Behavior</h4>
$showstart<p>
<form target='_top' action=prefshandler.php method=post>
<table class=trackertable>
<tr><td>Start Page:</td><td>$startpageselect<input type='submit' value='$updatelabel' name='rolehacksubmit' /></td>
</tr></table>
<h4>Appearance of My Pages</h4>
<table class=trackertable>
<tr><td>Header Layout:</td><td>$picslayoutselect<input type='submit' value='$updatelabel' name='picslayoutsubmit' /></td>
</tr>
<tr><td>Photo:</td><td><input type=text name=photoUrl value='$myphotoUrl' size=60 /><input type='submit' value='$updatelabel' name='photoUrlsubmit' /></td>
</tr>
<tr><td>Stylesheet:</td><td><input type=text name=stylesheeturl value='$mystylesheetUrl' size=60 /><input type='submit' value='$updatelabel' name='stylesheeturlsubmit' /></td>
</tr>
<tr><td>Affiliation:</td><td>$affselect<input type='submit' value='$updatelabel' name='affiliationgroupidsubmit' /></td>
</tr></table>
<h4>Billing Preferences</h4>
<table class=trackertable>
<tr><td>Fax options:</td><td>$faxselect<input type='submit' value='$updatelabel' name='faxsubmit' /></td>
</tr>
<tr><td>Charge class:</td><td>$chargeselect<input type='submit' value='$updatelabel' name='chargeclasssubmit' /></td>
</tr>
</td>
</tr>
</table>
</form>
</div>
XXX;
	return $div;
}

?>