<?
/**
 * A very simple substitute for the global MedCommons Tracking Number allocator.
 * 
 * It is not performant, nor secure, nor does it allocate tracking numbers in 
 * a globally unique way.   In other words, don't use this unless you actually
 * know what you are doing and don't plan to join the global MedCommons network (ever).
 *
 * @author ssadedin@medcommons.net
 */

require_once "securewslibdb.inc.php";
require_once "settings.php";

function exit_error($msg) {
   error_log("tracking number allocation failed due to:  $msg");
   header("HTTP/1.0 500 Internal Server Error - $msg"); 
   exit;
}

header("Pragma: no-cache");
header("Cache-Control: no-store, no-cache, must-revalidate");

if(!isset($GLOBALS['Enable_Local_Tracking_Number_Allocation'])) {
  exit_error("Local tracking number allocation disabled.  Please set Enable_Local_Tracking_Number_Allocation flag");
}

global $CENTRAL_HOST,$CENTRAL_DB,$CENTRAL_USER,$CENTRAL_PASS;
mysql_connect($CENTRAL_HOST, $CENTRAL_USER, $CENTRAL_PASS) or exit_error("can not connect to mysql");
mysql_select_db($CENTRAL_DB) or exit_error("can not connect to database $CENTRAL_DB");

$good = false;
$i=0;
while (($i<2000) && ($good===false))
{
  $tn = rand(100000,999999).rand(100000,999999);
  $query="select 1 from tracking_number where tracking_number = '$tn'";
  $result = mysql_query($query);
  if($result) {
    if(mysql_num_rows($result)==0) {
      $good = true;
      break;
    }
  }
  else
    exit_error("Failed to query tracking number table");

  $i++;
}

if($good!==false) 
  echo $tn;
else {
  exit_error("Failed to find new tracking number!");
}
?>
