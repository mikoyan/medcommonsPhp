<style type='text/css'>
  #accontainer ul {
    padding: 0px;
    margin: 0px;
  }
  #accontainer {
    top: 3em;
    width: 25em;
    left: 25em;
    clear: both;
  }
  input, input#mcid {
    width: 15em;
    position: relative;
  }
  hr#midbreak  {
  }
</style>
<div id="ContentBoxInterior"  mainId='page_setup' mainTitle="Services - MedCommons on Demand"  >

<h2>Providers with Default Consent to Temporary HealthURLs</h2>
<table id='svctable' title="providers with access to healthurls produced by service account <?=$accid?>">
	<tr><th>Provider ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>
  <?foreach($consents as $r2):?>
    <?$dellink = ($r2->friendmcid==$r2->mcid)?"can't delete":"<a href='?del=$r2->friendmcid' >delete</a>";?>
    <tr>
      <td><?=$r2->friendmcid?></td>
      <td><?if($r2->acctype=='GROUP'):?><?=htmlentities($r2->groupname)?><?else:?><?= htmlentities($r2->first_name).' '.htmlentities($r2->last_name)?><?endif;?></td>
      <td><?if($r2->acctype=='GROUP'):?>N/A<?else:?><?=htmlentities($r2->email)?><?endif;?></td>
      <td><?=$dellink?></td>
    </tr>
  <?endforeach;?>
</table>
<hr id='midbreak'/>
<h2>Add  New Provider</h2>
<div id=sharewith class=fform   >
<form action=svcconsents.php method=post>

<div class=inperr id=err><?=$v->err?></div>

<div class='field' style='z-index: 10;'>
  <span class=n>Group Name or Account ID</span>
  <span class=q>
    <input type='text' name='mcid' id='mcid' value='<?=$v->mcid?>'/>
    <span class=r><input type=submit name=submit class=altshort value='Lookup'/></span>
  </span>
  <div id="accontainer"></div>
</div>

<div class=field><span class=n>Provider Name</span>
<span class=q><input disabled='true' type='text' name='username' id='username' value='<?=$v->username?>' />
<span class=r><?=$acctype?> <?=$mobile?></span>
<div class='inperr' id='username_err'><?=$v->username_err?></div></span></div>
<div class='field'><span class=n>Provider Email</span>
<span class='q'><input disabled='true' type='text' name='useremail' id='useremail' value='<?=$v->useremail?>' />
<span class=r><?=$emailverified?></span>
<div class='inperr' id='useremail_err'><?=$v->useremail_err?></div></span></div>

<div class=field><span class=n>&nbsp;</span><span class=q><input type=submit <?=$adddisabled?> name='submit' class='mainwide' value='Add Provider' />&nbsp;
<input type=submit class='altshort' name=cancel  value='Cancel' /></span></div>
</form>
<table class=tinst>
<td class=lcol >Instructions</td><td class=rcol >These are account holders
who can access and manipulate temporary HealthURLs generated by 
Voucher Services for this account <?=$accid?>.<br/>
Additional providers can be added to each specific Voucher, but these 
individuals in this list consent to access every voucher issued by this 
account.</td></tr>
</table>
</div>
</div>
<script type="text/javascript" src="/zip/yui/2.6.0/yahoo-dom-event/yahoo-dom-event.js,yui/2.6.0/connection/connection-min.js,yui/2.6.0/json/json-min.js,yui/2.6.0/autocomplete/autocomplete-min.js,acct/sha1.js"></script>
<script type='text/javascript'>
var ac,ds;
window.onload = new function() {
    document.body.className = 'yui-skin-sam'; // because header is hard coded
    ds = new YAHOO.widget.DS_XHR("/acct/addresses.php", ["addresses","name"]);
    ds.queryMatchContains = true;
    ds.queryMatchSubset = true;
    ds.scriptQueryAppend = "enc="+hex_sha1(getCookie('mc'));

    // Instantiate AutoComplete
    ac = new YAHOO.widget.AutoComplete("mcid","accontainer", ds);
    ac.useShadow = true;
    ac.formatResult = function(r, q) {
        return r[1].name + " (" + r[1].accid + ")";
    };
    ac.itemSelectEvent.subscribe(function(type,args) {
      var group = args[2][1];
      document.getElementById('mcid').value=group.accid;
      document.getElementById('username').value=group.name;
      document.getElementById('useremail').value='N / A';
    });

};
</script>
<link rel="stylesheet" type="text/css" href="/zip/yui/2.6.0/fonts/fonts-min.css,yui/2.6.0/autocomplete/assets/skins/sam/autocomplete.css" />
