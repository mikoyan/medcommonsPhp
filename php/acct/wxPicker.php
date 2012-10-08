<?php

require_once "alib.inc.php";
function generate_code($accid,$email,$sections)
{
	$key = base64_encode(sha1($accid.$email).'|'.$accid.'|'.$email);
	$out = <<<XXX
<html><head><title>MedCommons Widget Maker</title>
<style type="text/css" media="all"> @import "main.css";</style> 
<style type="text/css" media="all"> @import "theme.css"; </style>
</head><body>
XXX;

	$out .="<b>Basic Popup Frame (requires login)</b>
                      <xmp><a target = '_new' href='/acct/MCWidget.php?s=$sections'>popup</a>
                      </xmp>";
	$out .="<b>Basic Inline Frame (requires login)</b>
                      <xmp><iframe width='500' src='/acct/MCWidget.php?s=$sections'></iframe>
                      </xmp>";
	$out .="<b>IDP Popup Frame (requires login from remote IDP domain)</b>
                      <xmp><a target = '_new' href='/acct/MCIdpWidget.php?s=$sections'>popup</a>
                      </xmp>";
	$out .="<b>IDP Inline Frame (requires login from remote IDP domain)</b>
                      <xmp><iframe width='500' src='/acct/MCIdpWidget.php?s=$sections'></iframe>
                      </xmp>";

	$out .="<b>Generalized Google Gadget (hack and host this XML at your site)</b>";
	$out .=<<<XXX
<xmp>
<Module>
  <ModulePrefs title="MedCommons - Widget" description="Customized Widget licensed by MedCommons"
    height="600" title_url="medcommons.net" 
    author="MedCommons" scaling="false" 
    render_inline="optional" 
    author_affiliation="http://www.medcommons.net"/>
 <UserPref name="mckey" required="true" 
           display_name="medcommons widget key (at www.medcommons.net/widgets)"/>
 <Content type="url" href="/acct/MCWidget.php?s=$sections"/>
</Module>
</xmp>
XXX;

	$out .="<b>Personalized Google Gadget (hack and host this XML on a personal page)</b>";
	$out .=<<<XXX
<xmp>
<Module>
  <ModulePrefs title="MedCommons - Widget" 
    description="Personalized Widget licensed by MedCommons"
    height="600" title_url="medcommons.net" 
    author="MedCommons" scaling="false" 
    render_inline="optional" 
    author_affiliation="http://www.medcommons.net"/>
 <Content type="url" 
    href="/acct/MCWidget.php?s=$sections&mckey=$key"/>
</Module>
</xmp>
XXX;

	$out .= "<b>Post Bookmark to del.icio.us<b>";
	$out .=<<<XXX
   <xmp>
   <a href="http://del.icio.us/post?url='/acct/MCWidget.php?s=$sections'
   &title="MedCommons Customized Widged">Bookmark This</a>
    </xmp>
XXX;
   $out .= "</body></html>";
   
   return $out;
}

function make_abbrev ($abbrev,$longer)
{
	$GLOBALS['abbrev'][$abbrev]=$longer;
	$GLOBALS['abbrevcount']++;
}

function emit_list($set,$colhead)
{


	$out =  "<ul id='$set' class='sortable boxy'><b>$colhead</b>";
	$count = $GLOBALS['layoutcount'];
	for ($i=0; $i<$count; $i++)
	{
		if ($GLOBALS['layout'][$i][0]==$set)
		{
			$out .= '<li id="'.$GLOBALS['layout'][$i][1].'">'.//$GLOBALS['layout'][$i][1].
			' '.$GLOBALS['abbrev'][$GLOBALS['layout'][$i][1]].'</li>';
		}

	}
	$out .= '</ul>';
	return $out;
}
function print_layout()
{

	$count = $GLOBALS['layoutcount'];
	for ($i=0;$i<$count;$i++)
	{   $item = $GLOBALS['layout'][$i][1];
	echo "<br>set=".
	$GLOBALS['layout'][$i][0].',item='.$item.',longer='.
	$GLOBALS['abbrev']["$item"];
	}
}
function encode_layout($set)
{
	$l='';
	$count = $GLOBALS['layoutcount'];
	for ($i=0;$i<$count;$i++)
	{
		if ($GLOBALS['layout'][$i][0]==$set)
		$l.=
		$GLOBALS['layout'][$i][1];
	}
	return $l;
}

function il($item,$set)
{  //echo "il item $item set $set valid ".$GLOBALS['valid']."</br>";
	if (strpos($GLOBALS['valid'],$item)!==false) // only if started with valid mask
	{
		$count=$GLOBALS['layoutcount'];$GLOBALS['layoutcount']; //no dupes
		$found=false;
		for ($j=0;$j<$count;$j++) if ($item == $GLOBALS['layout'][$j][1]) { $found=true; break;}
		if (!$found)
		{
			$GLOBALS['layout'][]=array($set,$item);
			$GLOBALS['layoutcount']++;
		}
	}
}

function parse_layout($data)
{
	//l ( A )
	//0 1 2 3
	$GLOBALS['layoutcount']=0;
	$containers = explode(":", $data);
	foreach($containers AS $container)
	{
		$len = strlen($container);  //4
		$strpos = strpos($container,'('); // first pos for list 1
		$set = substr($container,0,$strpos); // set =1
		$rest = substr ($container,$strpos+1,$len-$strpos-2);
		$eaches = explode(',',$rest);
		foreach ($eaches as $order)
		{
			//don't include cols without order
			if ($order!=''){
				$GLOBALS['layout'][] = array($set,$order);
				$GLOBALS['layoutcount']++;
			}
		}
	}
}

function init_layout()
{

	il ('c','right_col');
	il ('e','right_col');
	il ('i','right_col');
	il ('b','right_col');
	il ('r','right_col');
	il ('z','right_col');
	il ('y','right_col');
	il ('x','right_col');
	il ('g','right_col');
	il ('a','right_col');
	il ('p','right_col');
	il ('t','right_col');
	il ('d','right_col');
	il ('k','right_col');
	il ('l','right_col');
	il ('f','right_col');
	il ('n','right_col');
	il ('j','right_col');

}
//start here


$GLOBALS['layout']=array();
$GLOBALS['abbrev']=array();
$GLOBALS['abbrevcount']=0;
$GLOBALS['layoutcount']=0;

make_abbrev ('n','Healthcare News');
make_abbrev ('f','Account Log');
make_abbrev ('l','Systemwide Audit Log');
make_abbrev ('c','CCRs');
make_abbrev ('e','Emergency CCR');
make_abbrev ('i','Personal Information');
make_abbrev ('j','Personas');
make_abbrev ('b','Charges');
make_abbrev ('r','Extensions');
make_abbrev ('z','Healthcare Providers');
make_abbrev ('y','Provider Tasks');
make_abbrev ('x','Practice Group Administrative Tasks');
make_abbrev ('g','Memberships');
make_abbrev ('a','Group Administrative Tasks');
make_abbrev ('p','Preferences');
make_abbrev ('t','Trackers');
make_abbrev ('d','Key Documents');
make_abbrev ('k','Consents');

list($accid,$fn,$ln,$email,$idp,$coookie) = aconfirm_logged_in ("You must be logged on to make widgets:=)"); // does not return if not lo

$db = aconnect_db(); // connect to the right database


if(isset($_POST['order']))
{
	// come here when submite is entered
	parse_layout($_POST['order']); // parse to internal form
	$left = encode_layout('left_col');
	$el = $left.'|'
	//.encode_layout('center').'|'
	.encode_layout('right_col');
	// redirect so refresh doesnt reset order to last save
	//put_switches($accid,$el);
	//	die ('reenterd pickcomp on post');
	//	header("Location: goStart.php");
//	require_once 'goStart.php';
	echo generate_code($accid,$email,$left);

	exit;
}
//start here if fresh


list($el,$valid) = get_switches($accid);
if ($valid=='') $valid='abcdefghijklmnopqrstuvwxyz';
$GLOBALS['valid'] = $valid; //get all the valid entries we can show the user
$count = strlen($GLOBALS['valid']);
if ($el =='')
{// nothing setup so init the table

	init_layout();

}
else
{
	list ($left,$right) = explode('|',$el);  // split each apart
	$count = strlen($left); //
	for ($i=0; $i<$count; $i++) il(substr($left,$i,1),'left_col');
	//	$count = strlen($center); //
	//	for ($i=0; $i<$count; $i++) il('center',substr($center,$i,1));
	$count = strlen($right); //
	for ($i=0; $i<$count; $i++) il(substr($right,$i,1),'right_col');
	init_layout(); // add these things back in if not on either side yet and valid
}
$left = emit_list('left_col','My Widget Components:');
//$center = emit_list('center');
$right = emit_list('right_col','All Available Components:');


$body = <<<XXX

$left
$right
<form name='form' id='form' target='RIGHT' action="wxPicker.php" method="post">
<br />
<input type="hidden" name="$el" value=switches />
<input type="hidden" name="order" id="order" value="" />
<input type="submit" onclick="getSort();" value="Generate Code" />
</form>
XXX;


	$htmlhead = <<<XXX
<title>MedCommons Widget Maker</title>
<style type="text/css" media="all"> @import "main.css";</style> 
<style type="text/css" media="all"> @import "theme.css"; </style>
<style type="text/css">
#left_col {
    width: 180px;
    float: left;
    margin-left: 5px;
}

#center {
    width: 180px;
    float: left;
    margin-left: 5px;
}

#right_col {
    width: 180px;
    float: left;
    margin-left: 5px;
}
}

form {
  clear: left;
}
br {
        clear: left;
}
</style>
<style type="text/css" media="all"> @import "main.css";</style> 
<style type="text/css" media="all"> @import "theme.css"; </style>
<link rel="stylesheet" href="dd_files/lists.css" type="text/css">
<script language="JavaScript" type="text/javascript" src="dd_files/coordinates.js"></script>
<script language="JavaScript" type="text/javascript" src="dd_files/drag.js"></script>
<script language="JavaScript" type="text/javascript" src="dd_files/dragdrop.js"></script>
<script language="JavaScript" type="text/javascript">

window.onload = function() {

	var list = document.getElementById("left_col");
	DragDrop.makeListContainer( list, 'g1' );
	list.onDragOver = function() { this.style["background"] = "#EEF"; };
	list.onDragOut = function() {this.style["background"] = "none"; };


	list = document.getElementById("right_col");
	DragDrop.makeListContainer( list, 'g1' );
	list.onDragOver = function() { this.style["background"] = "#EEF"; };
	list.onDragOut = function() {this.style["background"] = "none"; };

};

function getSort()
{
	order = document.getElementById("order");
	order.value = DragDrop.serData('g1', null);
}

function showValue()
{
	order = document.getElementById("order");
	alert(order.value);
}
//-->
</script>
XXX;

	echo "<html><head><title>MedCommons Widget Maker</title>$htmlhead</head><body>$body</body></html>";


	//echo std("MedCommons Widget Maker","MedCommons Widget Maker for $accid",$htmlhead,false, stdlayout ($body));
?>
