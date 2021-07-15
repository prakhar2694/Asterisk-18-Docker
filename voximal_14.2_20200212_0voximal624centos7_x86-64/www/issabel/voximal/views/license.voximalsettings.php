<?php 
// File    : license.voximalsettings.php
// Version : $Revision: 1.1 $


global $astman;
if (isset($_POST['form'])) {
    $newkey=$_POST['key'];
    // In case of remote lic => reset key to clear in file
    if ($_POST['lic_mng'] == "remote")
        $newkey="";
	modifyVoximalLicense($_POST['key']);
	needreload();
}

$license = getVoximalLicense();
if ($astman) {
	$out = $astman->send_request('Command',array('Command'=>"voximal show license"));
	$out = explode("\n",$out['data']);

    foreach ($out as $line) {
        if (strpos($line,":") !== false) {
            $value = explode(":", $line);
            if (count($value) == 1)
            $value[1]="";
            list($key, $val) = $value;
            $val = trim(ltrim($val));
            if (isset($val) && $val != "")
            $license_infos[rtrim(ltrim($key))] = trim(ltrim($val));
        }
    }

    //print_r ($license_infos);
}

//print_r ($license);


if (!isset($license["licensekey"]) || $license["licensekey"]=='') {
    $license_mng    = "Remote";
    $remote_checked = "checked";
    $local_checked  = "";
    $dispRemoteLic  = "table-row";
    $dispLocalLic   = "none";
}
else {
    $license_mng = "Local";
    $remote_checked = "";
    $local_checked  = "checked";
    $dispRemoteLic  = "none";
    $dispLocalLic   = "table-row";
}

$licExpired        = false;
$licWithExpiration = false;
// Check expiration date
if (isset($license_infos["Expiration"])) {
    $elts       = explode(" ", $license_infos["Expiration"]);
    $expdate    = $elts[0];
    $now        = new DateTime(date("Y-m-d")); //date('Y-m-d'));
    $expiration = new DateTime($expdate); //$elt["last_req"]);
    $interval   = $now->diff($expiration);
     // pour avoir le nb de jours de diff avec signe : $interval->format('%R%a')
    $nbDayDiff = $interval->format('%R%a');
   //echo("$expdate => $nbDayDiff" );
    if ($nbDayDiff <= 0)
        $licExpired = true;
    else
        $licWithExpiration = true;
}



$help_lic_mng_status  = _("License manager status :<br><ul><li>Ok : The connection with license server is OK</li></ul>");

$tabindex = 0;
?>

<style TYPE = "text/css">
.showtable, .showtable TD, .showtable TH
{
font-family: verdana;
font-size: 10pt;
line-height: 0.5;
}
</style>


<form autocomplete="off" name="license" id="license" action="config.php?display=voximalsettings&view=license" method="post">
	<input type="hidden" id="form" name="form" value="form">
	<input type="hidden" id="previd" name="previd" value="1">
	<table width="700px">
		<tr>
			<td><h5><?php echo _("License");?><hr></h5></td>
		</tr>
	
   			<tr>
				<td style="width: 30%;"><a href="#" class="info">UID<span><?php echo _("This is the identification key for the IVR.");?></span></a></td>
				<td style="width: 70%;"><input type="text" id="ivr_id" name="ivr_id" style="width: 300px;" readonly value="<?php echo $license_infos["UID"]; ?>"></td>
			</tr>
   			<tr>
				<td style="width: 30%;"><a href="#" class="info"><?php echo _("License");?><span><?php echo _("The IVR can be manage Localy or Remotely.");?></span></a></td>
				<!-- <td style="width: 70%;"><?php echo $license_mng;?></td> -->
                <td>
                    <span class="radioset">
						<input id="lic_remote" type="radio" name="lic_mng" value="remote" <?php echo $remote_checked?> tabindex="<?php echo $tabindex++?>" onchange="licRemoteOnchange()">
							<label for="lic_remote">Remote</label>
						<input id="lic_local" type="radio" name="lic_mng" value="local" <?php echo $local_checked?> tabindex="<?php echo $tabindex++?>" onchange="licLocalOnchange()">
							<label for="lic_local">Local</label>
					</span>
				</td>

			</tr>
			<tr id="tr_maxSessions">
				<td style="width: 30%;"><a href="#" class="info"><?php echo _("Max sessions");?><span><?php echo _("Maximum simultaneous calls.");?></span></a></td>
				<td style="width: 70%;"><?php echo $license_infos["Max sessions"];?></td>
			</tr>
			<tr id="tr_tts">
				<td style="width: 30%;"><a href="#" class="info"><?php echo _("TTS");?><span><?php echo _("Text To Speech module status.");?></span></a></td>
				<td style="width: 70%;"><?php if (!strcasecmp($license_infos["TextToSpeech"],"Yes")) echo _("Enable"); else echo _("Disable")  ;?></td>
			</tr>
			<tr id="tr_asr">
				<td style="width: 30%;"><a href="#" class="info"><?php echo _("ASR");?><span><?php echo _("Voice recognition module status.");?></span></a></td>
				<td style="width: 70%;"><?php if (!strcasecmp($license_infos["Speech"],"Yes")) echo _("Enable"); else echo _("Disable");?></td>
			</tr>

			<tr id="tr_licMngStatus" style="display:<?php echo $dispRemoteLic?>">
				<td style="width: 30%;"><a href="#" class="info"><?php echo _("License status");?><span><?php echo $help_lic_mng_status;?></span></a></td>
                <?php if ($licExpired) { ?>
				<td style="width: 70%;"><b><span style="font-family: Verdana; color: red;"><?php echo _("Expired");?></span></b></td>
                <?php } else if ($licWithExpiration) { ?>
				<td style="width: 70%;"><span style="font-family: Verdana; color: orange;"><?php echo _("Expiration");?> : <?php echo $expdate;?></span></td>
                <?php } else { ?>
				<td style="width: 70%;"><?php echo $license_infos["State"];?></td>
                <?php }?>
			</tr>

			<tr id="tr_code" style="display: <?php echo $dispLocalLic?>">
				<td style="width: 30%;"><a href="#" class="info"><?php echo _("Code");?><span><?php echo _("This code is used to generate your local license key. Give it to support with UID to get your license key.");?></span></a></td>
				<td style="width: 70%;"><?php echo $license_infos["Code"]?></td>
			</tr>
			<tr id="tr_key" style="display: <?php echo $dispLocalLic?>">
				<td style="width: 30%;"><a href="#" class="info"><?php echo _("Key");?><span><?php echo _("Enter the key value for your license.");?></span></a></td>
				<td style="width: 70%;"><input type="text" id="key" name="key" style="width: 250px;" value="<?php echo $license["licensekey"]?>"></td>
			</tr>

			<tr>
				<td>
					<br>
					<table id="tab_buttons">
						<tr>
							<td><input type="submit" style="width: 80px; height: 25px;" value="<?php echo _("Save");?>"></form></td>
							<td><input type="button" style="width: 80px; height: 25px;" onclick="location.reload();" value="<?php echo _("Discard");?>"></td>
						</tr>
					</table>
					<br><br>
				</td>
			</tr>

	</table>
<?php if (isset($license_infos["State"]) && strcasecmp($license_infos["State"], "ok") != 0) { ?>
<br><br>
<span style="font-family: Verdana; color: red;"><?php echo _("WARNING: Your license is invalid. Please check with sales.");?></span>
<?php } ?>


<script type="text/javascript">

	function licRemoteOnchange() {

		document.getElementById("lic_local");
		document.getElementById("lic_remote");

		var form = document.getElementById("license");
		var lic_remote = document.getElementById("lic_remote");
    var lic_local  = document.getElementById("lic_local");

        if (lic_remote.checked) {
            var answer = confirm ("<?php echo _("Are you sure to switch to REMOTE license")?> ?");
            if (!answer) {
                lic_remote.checked = false;
                lic_local.checked = true;
            }
            else {
                //document.getElementById("tab_buttons").style.display = "none";
                document.getElementById("tr_licMngStatus").style.display = "table-row";
                document.getElementById("tr_maxSessions").style.display = "table-row";
                document.getElementById("tr_tts").style.display = "table-row";
                document.getElementById("tr_asr").style.display = "table-row";
                document.getElementById("tr_key").style.display = "none";
                document.getElementById("tr_code").style.display = "none";
                document.getElementById("key").value ="";
                //document.getElementById("tab_buttons").style.display = "none";
            }
        }
	}
	function licLocalOnchange() {

		document.getElementById("lic_local");
		document.getElementById("lic_remote");

		var form = document.getElementById("license");
		var lic_remote = document.getElementById("lic_remote");
		var lic_local  = document.getElementById("lic_local");

        if (lic_remote.checked)
            alert("licLocalOnchange: remote cheched");

        if (lic_local.checked) {
            var answer = confirm ("<?php echo _("Are you sure to switch to LOCAL license")?> ?");
            if (!answer) {
                lic_local.checked = false;
                lic_remote.checked = true;
            }
            else {
                /*var tab_buttons = document.getElementById("tab_buttons");
                tab_buttons.style.display = "table"; */
                document.getElementById("tab_buttons").style.display = "table";
                document.getElementById("tr_licMngStatus").style.display = "none";
                document.getElementById("tr_maxSessions").style.display = "table-row";
                document.getElementById("tr_tts").style.display = "table-row";
                document.getElementById("tr_asr").style.display = "table-row";
                document.getElementById("tr_key").style.display = "table-row";
                document.getElementById("tr_code").style.display = "table-row";
            }
        }


	}





</script>
