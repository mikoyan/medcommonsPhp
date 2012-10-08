<?php
// this works 2 ways
// if we have the specific requst ID then we can just display it for now
// if we have all the pieces coming from the website then insert into the database and then display
require_once "modpay.inc.php";
require_once "db.inc.php";
require_once "utils.inc.php";

$accid=0; // incase we get no further
$sv = $GLOBALS['appliance'] ;
$p1=strpos($sv,'//');
$p2 = strrpos($sv,'/');
$sv = substr($sv,$p1+2,$p2-($p1+2));
$customize='';

if (isset($_REQUEST['n'])) {
	$n = base64_decode($_REQUEST['n']);
	list($patient_name,$patient_dob,$patient_email,$patient_note,$provider_name,$provider_email,$svcname,$svcnum) = explode('|',$n);
	$vid = generate_voucher_id($sv);  // provably safe since generated internally

  dbg("Creating voucher $vid for service $svcnum");

	$customize = <<<XXX
<div class=topinfo>Please complete, sign, and bring this form to your health care provider.</div>
XXX;

  $db = DB::get();

  // There are two options for svcnum - it could be the number of the service if it is
  // a predefined service, or it could be the name
  if($svcnum !== "") {
    $svc = $db->first_row("select * from modservices where svcnum = ?",array($svcnum));
    if(!$svc)
      throw new Exception("Unable to locate service $svcnum");
    $servicename = $svc->servicename;
    $accid = $svc->accid;
  }
  else {
    $servicename = $svcname;
    $svcnum = null;
  }

	// alright, once we have a voucherid lets shovel it into the database
  $db->execute("insert into modroi ( reqid, issued, patientname, patientemail, 
                                     patientdob, patientnote, providername, provideremail, svcnum, servicename)
                values (?,NOW(),?,?,?,?,?,?,?,?)", 
                array($vid,$patient_name,$patient_email, $patient_dob, $patient_note, $provider_name, $provider_email, $svcnum, $servicename));
}
else if (isset($_REQUEST['reqid'])) // already supplied, just look it up
{
  $db = DB::get();
  $r = $db->first_row("select * from modroi where reqid = ?",array(req('reqid')));
	if($r===false)
    throw new Exception("Unable to locate roir req ".req('reqid'));

  $accid = 0;
  if($r->svcnum) {
    $svc = $db->first_row("select * from modservices where svcnm = ?",array($r->svcnum));
    if(!$svc)
      throw new Exception("Unablet to locate service $r->svcnum for roi request $r->reqid");

    $accid = $svc->accid;
  }

	$patient_name= $r->patientname ;
	$patient_dob= $r->patientdob != "0000-00-00" ? $r->patientdob : '' ;
	$patient_email= $r->patientemail ;
	$patient_note= $r->patientnote ;
	$provider_name= $r->providername ;
	$provider_email= $r->provideremail ;
  $servicename = $r->servicename;
	$vid  = $r->reqid ;
	$getacct = "/personal.php";
	if($accid) {
		$services = requested_services ($accid,$r->servicename);
		$arg = base64_encode("$r->patientname|$r->patientemail|$r->patientnote|$r->svcnum|$r->servicename");
		$getacct = "/mod/vouchersetup.php?roi=$arg";
  } 
  else 
    $accid=0;

	$customize = <<<XXX
<div class=topinfo>
This is a request for provider services previously generated by $patient_name. <br/>
If you have a <a href='$getacct'>MedCommons Account</a>, you can charge money for this service to your patients.
</div>
XXX;

}
else die ("Invalid Startup Parameters for roireq");

$tophead = <<<XXX
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<!-- 
MedCommons ROI Request for  Provider Services  running on $sv

Service initiated by the patient $patient_name - $provider_name

  Copyright MedCommons 2008
 -->
<html>
<head>
    <title>HIPAA Release of Information Request - MedCommons</title>
    <link rel="stylesheet" type="text/css" href="/css/cover.css"/>
    <style>
    .prompt {font-weight:500; text-align:left; width:30em;float:left;}
    .right  {font-weight:700; text-align:left; width: 50em; }
    .boldhead {font-weight:700; font-size: 1.3em;padding-bottom:20px;}
    .bigger {font-size:1.6em;}
    .bitbigger {font-size:1.1em;}
    .bold {font-weight:700}
    .topinfo {margin:20px; padding:20px; border:1px solid; background-color:#DFF}
    </style>
</head>
<body  onload="window.focus(); window.print();" >
<div style='margin:10px; padding:10px; border:none;'>
$customize
XXX;
$pickup = (isset($GLOBALS['voucher_pickuproiurl']))?$GLOBALS['voucher_pickuproiurl']:'must upgrade local_mod_config on this server to include voucher_pickuproiurl';
if($patient_note!='') 
  $note="<br/><p class='bitbigger'><span class='bold'>Note:</span> ".htmlentities($patient_note)."</p>"; 
else 
  $note='';

$markup = <<<XXX
<span class=boldhead>AUTHORIZATION FOR RELEASE OF MEDICAL INFORMATION</span></br>
<br/>
<span class=prompt>Patient's Name</span><span class=right>$patient_name</span><br/>
<span class=prompt>Date of Birth</span><span class=right>$patient_dob</span><br/>
<span class=prompt>Email</span><span class=right>$patient_email</span><br/><br/>
<span class=boldhead>SERVICES REQUESTED </span><br/><br/>

XXX;
$markup.=requested_services($accid, $servicename);
if ($note!='') $markup.="$note <br/>";
$markup .=<<<XXX
<div style='margin:16px 0px 10px 0px; padding:10px; border:1px solid;'>

<span class=boldhead>ELECTRONIC DELIVERY REQUEST</span>
<p>I request private communications of medical records as identified above. I would prefer a 
paperless transfer via a temporary, HIPAA-compliant personal health record.</p>

<p>Request ID Code: &nbsp;&nbsp;&nbsp;<span class=bigger>$vid</span></p>

<p>Service providers can register to service on-line records requests at 
www.medcommons.net. The service voucher you create will enable you to fax these 
records to a HIPAA-compliant temporary account as well as upload standard PDF, 
CCR and DICOM imaging. The provider can set a charge for this service when the 
patient's voucher is printed.</p>

<p>Charges associated with this service can be collected from the patient on-line 
prior to records release.</p>

</div>
<br/>
<br/>

Signature of Patient or Representative &nbsp;_________________________________________ &nbsp;&nbsp;&nbsp;&nbsp;   Date &nbsp;_____________
<br/>
<br/>
Relationship to Patient &nbsp;&nbsp;________________________ 
<br/>
<br/>
<hr/>
(c) 2008 MedCommons Inc.

XXX;

echo $tophead.$markup."</div></body></html>";



?>
