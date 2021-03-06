<?php

require_once "setup.inc.php";
require_once './OAuth.php';
require_once 'mc_oauth_client.php';

function islog($type,$openid,$blurb) 
		{
		$time = time();
		$ip = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];
		dosql("Insert into islog set time='$time',ip='$ip',id='$openid',type='$type', url='$blurb' ");
		}
function isdb_fetch_object($result)
{
	return mysql_fetch_object($result);
}
function isdb_fetch_array($result)
{
	return mysql_fetch_array($result);
}

function  isdb_insert_id()
{
	return mysql_insert_id();
}

function dosql($q)
{
	if (!isset($GLOBALS['db_connected']) ){
		$GLOBALS['db_connected'] = mysql_connect($GLOBALS['DB_Connection'] ,$GLOBALS['DB_User'] );
		$db = $GLOBALS['DB_Database'];
		mysql_select_db($db) or die ("can not connect to database $db ".mysql_error());
	}
	$status = mysql_query($q);
	if (!$status) die ("dosql failed $q ".mysql_error());
	return $status;
}
function clean($s)
{
	return mysql_real_escape_string(trim($s));
}

function getplayerbyind ($playerind)
{
	$result = dosql("Select * from players where playerind='$playerind'");
	$r=isdb_fetch_object($result);
	return $r;
}
function get_appl_record($table,$op, $ascending,$ind)
{           $desc = ($ascending) ?'':'desc';
$result = dosql( "select * from $table where ind $op '$ind' order by ind $desc limit 1 " ); // overspecifiy for safety
$r=isdb_fetch_object($result);
return $r;
}
function getteambyname($team){
	$result = dosql ("Select * from teams where name='$team' ");
	$r=isdb_fetch_object($result);
	return $r;
}
function getleagueteambyteamind($teamind){
	$result = dosql ("Select * from leagueteams where teamind='$teamind' ");
	$r=isdb_fetch_object($result);
	return $r;
}
function getallusers($clause)
{
	return dosql("Select * from users where $clause ") ;
}
function  fetch_alerts($minprio,$playerind,$teamind) {
	$qqq= "select * from alerts where playerind='$playerind' and teamind='$teamind' and  priority >= '$minprio' order by alertind desc limit 20 ";
	return dosql($qqq);
}
function  fetch_query_templates($leagueind,$plugid) {
	$qqq= "select l.name,a.* from leagues l,  qtemplates a,leagueteams lt where plugid='$plugid'
	                           and a.teamind=lt.teamind and lt.leagueind='$leagueind'
	                            and l.ind=lt.leagueind order by alertind desc limit 20 ";
	return dosql($qqq);
}
function getplayerbyname($player,$team)
{	$player = mysql_escape_string($player);
$r = dosql("SELECT * from players where name='$player' and team='$team'");
$f = isdb_fetch_object($r);
return $f;
}
function get_appl_record_ind($table,$ind)
{
	$result = dosql("Select * from $table where ind='$ind' "); // reread the record we just inserted
	$r=isdb_fetch_object($result);
	return $r;
}


function playernamefromind ($ind)
{
	$result = dosql("Select * from players where playerind = '$ind'");
	$rr = isdb_fetch_object($result);
	if ($rr==false) return false; else return array($rr->name,$rr->team);
}
function teamnamefromind ($ind)
{
	$result = dosql("Select * from teams where teamind = '$ind'");
	$rr = isdb_fetch_object($result);
	if ($rr==false) return false; else return $rr->name;
}
function firstplayer($team)
{
	$teamind =get_teamind($team);
	$result = dosql("SELECT playerind from teamplayers where teamind='$teamind' limit 1");
	$f = isdb_fetch_object($result);
	return $f->playerind;
}
function get_playerind ($player)
{
	$player=mysql_escape_string($player);
	$result = dosql("Select * from players where name='$player' ");
	$r = isdb_fetch_object($result);
	if ($r===false) return false; return $r->playerind;
}
function get_teamind ($team)
{
	$result = dosql("Select * from teams where name='$team' ");
	$r = isdb_fetch_object($result);
	if ($r===false) return false; return $r->teamind;
}
function getleagueind($league)
{
	$result = dosql("Select ind from leagues where name='$league' ");
	$r = isdb_fetch_object($result);
	if ($r==false) return false;
	return $r->ind;
}
function getLeague($team)
{
	$result= dosql("SELECT l.logourl,l.ind,l.name
	from teams t, leagueteams lt, leagues l 
	where t.name='$team' and t.teamind=lt.teamind and lt.leagueind=l.ind");
	$f = isdb_fetch_object($result);
	if (!$f) return false;
	return $f;
}
function getleaguebyname($leaguename)
{
	$result = dosql("Select * from  leagues where name='$leaguename'");
	$rr = isdb_fetch_object($result);
	return $rr;
}
function getleaguebyind($leagueind)

{
	$result = dosql("Select * from  leagues where ind='$leagueind'");
	$rr = isdb_fetch_object($result);
	return $rr;
}
function plugidFromReport($report)
{
	return intval($report/3)+1;
}
// sql fence
function javascriptstuff()
{
	$javascriptstuff = <<<XXX
<script>
function showhide(id){
if (document.getElementById){
obj = document.getElementById(id);
if (obj.style.display == "none"){
obj.style.display = "";
} else {
obj.style.display = "none";
}
}
}
</script> 
XXX;
	return $javascriptstuff;
}
function makevarname($s)
{
	return str_replace(array(' ','/','$','-'),array('_','_','_','_'),$s);
}
function my_identity()
{
	if (!isset($_COOKIE['u']))
	{
		return "NotLoggedIn";
	}
	return  urldecode($_COOKIE['u']);
}

function redirect($url)
{
	islog('redirect to',my_identity(),$url);
	header("Location: $url");
	exit;
}
function check_login($roles,$blurb)
{
	$time = time();
	$ip = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];
	if (!isset($_COOKIE['u'])) $openid='--no cookie--'; else
	$openid = urldecode($_COOKIE['u']);
islog('check',$openid,"roles:$roles page:$blurb");
	if (!isset($_COOKIE['u']))
	{
		redirect("index.php?logout=checkloginnocookie");
		//echo "redirecting to index.php?logout";
		//exit;
	}
	$openid = urldecode($_COOKIE['u']);
	$result = dosql ("Select * from users where openid='$openid'  ");

	$r=isdb_fetch_object($result);

	if (!$r)
	{
		redirect("index.php?logout=nouser");
		//echo "redirecting to index.php?logout&err=nouser";
		//exit;
	}

	$urole = $r->role;
	$role = explode(',',$roles);
	$count = count($role);
	for ($j=0; $j<$count; $j++)
	if ($role[$j] == $urole)
	{
		islog('view',$openid,$blurb);
		return $urole;
	}

	redirect("index.php?logout=badrole+$urole+$roles");


	//echo "redirecting to index.php?logout&err=badrole";
	//exit;
}

function teamchooser($leagueind,$myteam,$id)
{  // the id tag for the select should probably be changed

	// returns a big select statement
	$outstr = <<<XXX
	<select $id name='team' title='choose another team in this league' onchange="location = 't.php?teamind='+this.options[this.selectedIndex].value;">
XXX;
	//$outstr = "<select name='team'>";
	$result = dosql ("SELECT t.name,t.teamind from teams t, leagueteams lt where lt.leagueind='$leagueind'  and lt.teamind = t.teamind
	                                                               order by t.name");

	while ($r2 = isdb_fetch_object($result))
	{
		$name = $r2->name;
		//$ename = urlencode($name);
		$selected = ($name == $myteam)?' selected ':'';
		$outstr .="<option value='$r2->teamind' $selected >$name</option>
		";
	}
	$outstr.="</select>";
	return $outstr;

}

function teamchooserind($leagueind,$id)
{
	// returns a big select statement . adds  one extra choice
	$outstr = <<<XXX
	<select  $id name='teamind' title='choose another team in this league' onchange="location = 't.php?teamind='+this.options[this.selectedIndex].value;">
	<option value='-1' >-choose team-</option>
XXX;
	$result = dosql ("SELECT t.teamind,t.name from teams t, leagueteams lt where lt.leagueind='$leagueind'  and lt.teamind = t.teamind
	                                                               order by t.name");

	while ($r2 = isdb_fetch_object($result))
	{
		$tind = $r2->teamind;
		$name = $r2->name;
		//$ename = urlencode($name);

		$outstr .="<option value='$tind' >$name</option>
		";
	}
	$outstr.="</select>";
	return $outstr;

}
function visualteamchooser($leagueind)
{

	$outstr = <<<XXX
	<div  id='leagueroster'   title='choose teams' >
XXX;
	$result = dosql ("SELECT t.teamind,t.name,t.logourl from teams t, leagueteams lt where lt.leagueind='$leagueind'  and lt.teamind = t.teamind   order by t.name");

	while ($r2 = isdb_fetch_object($result))
	{
		$tind = $r2->teamind;
		$name = $r2->name;
		$img  = $r2->logourl;
		if ($img =='')  $img= $GLOBALS['missing_image'];
		//$ename = urlencode($name);

		$outstr .="<div class='league_member'>
		<a href='t.php?teamind=$tind' title='open team roster page for $name' class='team_image' >
		<img src='$img' width='50' alt='no image for team' /></a>
		<a href='t.php?teamind=$tind' title='open team roster page for $name' class='team_name' >$name</a>
		</div>
		";
	}
	$outstr.="
	</div>
	";
	return $outstr;

}
function teamchooserindquiet($leagueind,$id)
{
	// returns a big select statement . adds  one extra choice
	$outstr = <<<XXX
	<select   $id name='teamind' title='choose another team in this league' >
	"<option value='-1' >-all teams-</option>
	"
XXX;
	$result = dosql ("SELECT t.teamind,t.name from teams t, leagueteams lt where lt.leagueind='$leagueind'  and lt.teamind = t.teamind
	                                                               order by t.name");

	while ($r2 = isdb_fetch_object($result))
	{
		$tind = $r2->teamind;
		$name = $r2->name;
		//$ename = urlencode($name);

		$outstr .="<option value='$tind' >$name</option>
		";
	}
	$outstr.="</select>";
	return $outstr;

}

function leaguechooser()
{
	// returns a big select statement

	$outstr = <<<XXX
	<select name='leagueind' title='choose another league on this informed sports service' onchange="location = 'l.php?leagueind='+this.options[this.selectedIndex].value;">
XXX;

	$result = dosql ("SELECT name, ind from leagues order by name");


	while ($r2=isdb_fetch_object($result))

	{
		$tind = $r2->ind;
		$name = $r2->name;
		//$ename = urlencode($name);

		$outstr .="<option value='$tind' >$name</option>";
	}
	$outstr.="</select>";
	return $outstr;

}
function playerchooser($team, $player)
{
	// returns a big select statement
	$outstr = <<<XXX
	<select id='playerselect' name='name' title='choose another player on $team' onchange="location = 'p.php?name='+this.options[this.selectedIndex].value;">
XXX;
	$result = dosql ("SELECT * from players  where team = '$team'");
	$eteam = urlencode ($team);
	while ($r2 = isdb_fetch_object($result))
	{
		$name = $r2->name;
		//$ename = urlencode($name);
		$selected = ($name == $player)?' selected ':'';
		$outstr .="<option value='$name' $selected >$name</option>
		";
	}
	$outstr.="</select>";
	return $outstr;

}function playerchooserquiet($team, $player)
{
	// returns a big select statement
	$outstr = <<<XXX
	<select id='playerselect' name='name' title='choose  player on $team' >
XXX;
	$result = dosql ("SELECT * from players  where team = '$team'");
	$eteam = urlencode ($team);
	while ($r2 = isdb_fetch_object($result))
	{
		$name = $r2->name;
		//$ename = urlencode($name);
		$selected = ($name == $player)?' selected ':'';
		$outstr .="<option value='$name' $selected >$name</option>
		";
	}
	$outstr.="</select>";
	return $outstr;

}
function playerchooserind($team, $player)
{
	// returns a big select statement
	$outstr = <<<XXX
	<select id='playerselect' name='playerind' title='choose another player on $team' onchange="location = 'p.php?playerind='+this.options[this.selectedIndex].value;">
XXX;
	$result = dosql ("SELECT * from players  where team = '$team'");

	$eteam = urlencode ($team);
	while ($r2 = isdb_fetch_object($result))
	{

		$name = $r2->name;
		//$ename = urlencode($name);
		$selected = ($name == $player)?' selected ':'';
		$outstr .="<option value='$r2->playerind' $selected >$name</option>
		";

	}
	$outstr.="</select>";
	return $outstr;

}
function trainerchooser($team)
{ return teamroleuserchooser($team,'team') ;}
function teamroleuserchooser($team,$role)
{
	// returns a big select statement
	$teamind=get_teamind($team);

	$outstr = "<select name='name'>";
	$result = dosql ("SELECT * from users  where teamind= '$teamind' and role='$role' ");


	while ($r2 = isdb_fetch_object($result))
	{

		//$ename = urlencode($name);
		$selected ='';// ($name == $player)?' selected ':'';
		$outstr .="<option value='$r2->email $r2->openid' $selected >$r2->openid</option>
		";

	}
	$outstr.="</select>";
	return $outstr;

}


function clean1($s)
{

	$s = preg_split("/[\s,]+/",$s);
	return clean($s[0]);
}

function userpagefooter()
{ // starts with tail end of the linkarea section
	$ret = <<<XXX
 <div id='footer'><a href="index.php" title="Informed Sports">Informed Sports</a>
 is built on the <a href='https://www.medcommons.net/'>MedCommons Appliance</a>
 <br/>
For more information  please contact <a href='http://informedsports.com/'>Sirius Healthcare</a>
</div>
XXX;
	return $ret;
}
function teamfooter($team)
{
	$teamind = get_teamind($team);
	$userpagefooter=userpagefooter();
	$result = dosql ("Select * from teams where teamind='$teamind'");
	$rr = isdb_fetch_object($result);
	if ($rr)
	{
		if ($rr->schedurl!='')$sched = "
&nbsp;|&nbsp;<a target='_new' target='_new' href='$rr->schedurl' title='schedule  $team'>sched</a>";
		else $sched='';
		if ($rr->newsurl!='')$news = "
&nbsp;|&nbsp;<a target='_new' target='_new' href='$rr->newsurl' title='news for  $team'>rss</a>";
		else $news='';
		$eteam=urlencode($team);
		// the open linkarea div gets closed inside userpagefooter()
		$x=<<<XXX
    <div id='linkarea'>
    <a href='t.php?teamind=$teamind' >team</a>$sched 
    $news&nbsp;|&nbsp;<a target='_new' href='launchsf.html?team=$eteam' title='support on salesforce.com for $team in new window'>support</a>
&nbsp;|&nbsp;<a href='index.php?logout=footer' title='logout from informed sports'>logout</a>
</div>
$userpagefooter
XXX;

		return $x;
	}
	return false;
}





function istoday($time)
{
	$now = date('Y-m-d h:i A');
	//echo "Now $now     time $time<br/>";
	return (substr($time,0,10)==substr($now,0,10));
}
function isyesterday($time)
{
	$now = time();
	$yesterday =
	date('Y-m-d h:i A',$now- (24 * 60 * 60));
	return (substr($time,0,10)==substr($yesterday,0,10));
}
function nicetime ($time)
{

	if (istoday($time)) return substr($time,11,8);
	else
	if (isyesterday($time)) return "yesterday";
	else return substr($time,5,2).'/'.substr($time,8,2).'/'.substr($time,2,2);

}

function allteamchooser($id)
{
	// returns a big select statement
	$outstr = "<select $id name='teamind'>
	";
	$result = dosql ("SELECT t.name,t.teamind,l.name from teams t, leagueteams lt, leagues l where  lt.teamind = t.teamind and lt.leagueind=l.ind
	                                                               order by l.name, t.name");
	$first = true;
	while ($r2 = isdb_fetch_array($result))
	{
		$team = $r2[0]; $teamind = $r2[1]; $league = $r2[2];
		//$ename = urlencode($name);
		$selected = ($first)?' selected ':'';
		$outstr .="<option value='$teamind' $selected >$league:$team</option>
		";
		$first = false;
	}
	$outstr.="</select>";
	return $outstr;
}
function alertlist($tag,$team,$player,$result, $tableid,$flavor)
{
	$counter = 0;
	$outstr= "   <div id='ajaxarea'>
	 $tag
	<table id='$tableid' class='is_alerts' >
	";

	$lastplayer=false;
	while ($r=isdb_fetch_object($result))
	{
		switch ($r->priority)
		{
			case '0': { $prio="normal"; break; }
			case '1': { $prio="high"; break; }
			case '2': { $prio="critical"; break; }
			default : { $prio="bad"; break; }

		}
		$atext = $r->text;
		$playerind = $r->playerind;
		switch ($r->type)
		{
			case '-2': { $type="query"; $useropenid = urldecode($r->useropenid);
			$atext ="<a title='view query template authored by $useropenid' href='p.php?plugid=$r->plugid&edit=$r->relatedind&playerind=$playerind' >$r->text</a> ";
			break; }
			case '-1': { $type="report"; $useropenid = urldecode($r->useropenid);
			$atext ="<a title='view report details authored by $useropenid' href='p.php?plugid=$r->plugid&edit=$r->relatedind&playerind=$playerind' >$r->text</a> ";
			break; }
			case '0': { $type="general"; break; }
			case '1': { $type="medical"; break; }
			case '2': { $type="head"; break; }
			case '3': { $type="cervical"; break; }
			case '4': { $type="upper"; break; }
			case '5': { $type="torso"; break; }
			case '6': { $type="lower"; break; }
			default : { $type="bad"; break; }

		}

		$v = playernamefromind($playerind);
		$player = $v[0];

		$playerurl = "p.php?playerind=$playerind";
		$playercell = '';//"<td><a href='$playerurl' title='Informed Sports page for $player'>$player</a></td>";

		$time = nicetime($r->time);
		$css_class="_pri_$prio";
		//if (($flavor ==0) || (($flavor>0) && ($lastplayer !== $player))) // if flavor >0 then only put out first for each
		$outstr.="<tr class='$css_class'>$playercell<td>$time</td><td title='$r->priority alert generated $r->time' >$type</td><td>$atext</td></tr>
		";
		$counter++;
		$lastplayer = $player;
	}
	$outstr.='</table></div>';
	if ($counter==0) $outstr="<div id='ajaxarea'>No $tag</div>";
	return $outstr;
}

function querylist($plugid,$tag,$leagueind, $result, $classid,$flavor)
{
	$plugin = getPluginInfo($plugid);
	$rr = mysql_fetch_object($plugin);

	$league = getleaguebyind($leagueind);

	$counter = 0;
	$outstr= "   <div id='ajaxarea'>
	 $tag
	<table id='$classid' class='is_stored_query' >
	";

	while ($r=isdb_fetch_object($result))
	{
		switch ($r->priority)
		{
			case '0': { $prio="normal"; break; }
			case '1': { $prio="high"; break; }
			case '2': { $prio="critical"; break; }
			default : { $prio="bad"; break; }

		}
		$atext = $r->text;

		$url = "dw.php?qlike=$r->relatedind&plugid=$rr->ind&leagueind=$league->ind";


		switch ($r->type)
		{
			case '-2': { $type="query"; $useropenid = urldecode($r->useropenid);
			$atext ="<a title='query for similar cases' href='$url' >$r->text</a> ";
			break; }

			default : { $type="bad"; break; }

		}



		$time = nicetime($r->time);
		$css_class="_pri_$prio";
		//if (($flavor ==0) || (($flavor>0) && ($lastplayer !== $player))) // if flavor >0 then only put out first for each
		$outstr.="<tr class='$css_class'><td>$time</td><td title='$r->priority alert generated $r->time' >$type</td><td>$atext</td></tr>
		";
		$counter++;

	}
	$outstr.='</table></div>';

	if ($counter==0) $outstr="<div id='ajaxarea'>No $tag</div>";
	return $outstr;
}

function injurylistplayer($tag,$playerind, $rr)
{
	$result= dosql("select * from $rr->table  where playerind='$playerind' order by playerind, ind desc limit 20");
	$counter = 0;
	$outstr= "<div id='ajaxarea'><h4>$rr->label</h4><table id='$rr->table' class='is_injurylist' >";
	$lastplayer=false;
	while ($r=isdb_fetch_object($result))
	{
		$blurb = blurb($r,"unspecified $rr->type");
		$time = strftime('%D %T',($r->time));
		$useropenid = urldecode($r->useropenid);
		$outstr.="<tr><td>$time</td><td><a title='view $rr->type case report entered by $useropenid' href='p.php?playerind=$playerind&plugid=$rr->ind&edit=$r->ind' >$blurb</a></td></tr>
		";
		$counter++;
	}
	$outstr.='</table></div>';
	if ($counter==0) $outstr="<div id='ajaxarea'>No $tag</div>";
	return $outstr;
}


function dbg($m) {
	error_log("XXX: $m");
}
function querychooser($leagueind,$plugid,$classidstuff)
{
	return querylist($plugid,"League-wide Stored Queries", $leagueind,  fetch_query_templates($leagueind,$plugid),$classidstuff,0);
}
function main_logo($my_role)
{
	if ($my_role=='is') $mimg = "<a href='is.php'><img width=200 src='images/Logo-IS.gif' alt='main logo'/></a>
	"; else $mimg = "<a href='#'><img width=200 src='images/Logo-IS.gif'  alt = 'main.logo' /></a>
	";
	return "<div id='logo'>".$mimg."</div>";
}

function league_logo($league,$my_role)
{  // hack to show in ie

	$myid = my_identity();
	if ($league->logourl!='')
	$imgurl =$league->logourl;
	else $imgurl= $GLOBALS['missing_image'];
	if ($my_role=='is'||$my_role=='league')
	$leagueimg = "<a href='l.php?leagueind=$league->ind' title='you are signed on as $myid role $my_role' ><img src='$imgurl' id='leagueimg' alt='you are signed on as $myid role $my_role' border='0' /></a>";
	else 	$leagueimg = "<a href='#' title='you are signed on as $myid role $my_role' ><img src='$imgurl' alt='you are signed on as $myid role $my_role' border='0' /></a>";
	return  "<div id='leaguelogo' >".$leagueimg."</div>"; // this div misnamed - check with michael
}

function countof($q)
{
	$result = dosql("Select count(*) from $q ");
	$r = isdb_fetch_array($result);
	if (!$r) return -1;
	return $r[0];
}

function getstats()
{
	$lc = countof ("leagues");
	$tc = countof ("teams");
	$pc = countof ("players");
	$hu = countof ("players where healthURL!='' ");
	$tr = countof ("users where role='team' ");
	$lm = countof ("users where role='league' ");
	$us = countof ("users where role='is' ");
	$al = countof ("alerts");
	return array($lc,$tc,$pc,$hu,$tr,$lm,$us,$al);
}
function  getAllPluginInfo($leagueind)
{
	$plugins = dosql("select * from plugins,leagueplugins where plugins.ind=leagueplugins.plugin and showonmenu='1' and leagueind='$leagueind' ");
	return $plugins;
}
function  getPluginInfo($id)
{
	$plugin = dosql("select * from plugins where plugins.ind='$id' ");
	return $plugin;
}

function blurb ($r,$blurbdef)
{
	$blurb = '';
	if (isset($r->Background_Info_short_case_description)) $blurb.="$r->Background_Info_short_case_description ";
	if (isset($r->Predicted_Outlook_)) $blurb.="$r->Predicted_Outlook_ ";
	$blurb = trim($blurb);	if ($blurb == '') $blurb = $blurbdef;
	return $blurb;
}

/**
 * Create and return an appliance API object initialized
 * for the given oauth access token for the given appliance. 
 *
 * @param appliance - the base url of the appliance
 * @param oauth_token - <key>,<secret> of access token
 */
function get_appliance_api($appliance,$oauth_token) {
	$token_parts = explode(",",$oauth_token);
	if(count($token_parts)!=2)
	throw new Exception("token $oauth_token is in an invalid format");
	return new ApplianceApi($GLOBALS['appliance_access_token'],$GLOBALS['appliance_access_secret'],$appliance, $token_parts[0], $token_parts[1]);
}

function get_request_token($appliance,$accid) {
	$api = new ApplianceApi($GLOBALS['appliance_access_token'],$GLOBALS['appliance_access_secret'],$appliance);
	return $api->get_request_token($accid);
}

/**
 * Return a version of $x escaped for javascript
 */
function jsesc($x) {
	return preg_replace("/\n/","\\n",addslashes($x));
}
?>
