<?php
// File    : general.voximalsettings.php
// Version : $Revision: 1.1 $


if (isset($_POST['form']) && $_POST['form'] == "editsettings") {
	$settings = array (
			"recordsilence" => trim($_POST['recordsilence']),
			"threshold" => trim($_POST['threshold']),
			"wavdefaultcodec" => $_POST['wavdefaultcodec'],
			"debug" => trim($_POST['debug']),
			"monitor" => trim($_POST['monitor']),
			"priorityevents" => trim($_POST['priorityevents']),
			"cachetimeout"  => trim($_POST['cachetimeout']),
			"dialformat"  => trim($_POST['dialformat']),
			"blindapplication"  => trim($_POST['blindapplication']),
			//"logsapache"    => trim($_POST['logsapache']),
			//"logsvoximal"   => trim($_POST['logsvoximal']),
 			//"logsvxml"      => trim($_POST['logsvxml'])
			"speechprovider" => trim($_POST['speechprovider']),
			"speechbeeps" => trim($_POST['speechbeeps']),
	);
	//modifyVoximalConfiguration($settings);
	saveGeneralConf($settings);
	needreload();
}

// Get Voximal license
global $astman;
if ($astman) {
	$out = $astman->send_request('Command',array('Command'=>"voximal show license"));
	$out = explode("\n",$out['data']);

    foreach ($out as $line) {
        if (strpos($line,":") !== false) {
            $value = explode(":", $line);
            if (count($value) == 1)
            $value[1]="";
            list($key, $val) = $value;
            $license_infos[rtrim(ltrim($key))] = trim(ltrim($val));
        }
    }
    //print_r ($license_infos);
	$out = $astman->send_request('Command',array('Command'=>"voximal show version"));
	$out = explode("\n",$out['data']);
    //print_r($out);
    foreach ($out as $line) {
        list($key, $val) = explode(":", $line);
        if (strcasecmp(rtrim(ltrim($key)), "Version") == 0) {
            $version_voximal = $val;
            break;
        }
    }
}



// GEt voximal configuration
//$settings = getVoximalConfiguration('*');
$settings = getGeneralConf(true);
//print_r($settings);

//We check the value of WAV default codec
if (trim($settings['wavdefaultcodec']) == "gsm") {
	$wavcodec_pcm = "";
	$wavcodec_gsm = "checked";
} else {
	$wavcodec_pcm = "checked";
	$wavcodec_gsm = "";

}

//We check the value of Debug
if (trim($settings['debug']) == "yes") {
	$debug_yes   = "checked";
	$debug_no    = "";
	$debug_debug = "";
}
else if (trim($settings['debug']) == "interpreter") {
	$debug_yes   = "";
	$debug_no    = "";
	$debug_debug = "checked";
}
else {
	$debug_yes   = "";
	$debug_no    = "checked";
	$debug_debug = "";
}

//We check the value of Debug
if (trim($settings['monitor']) == "yes") {
	$monitor_yes   = "checked";
	$monitor_no    = "";
}
else {
	$monitor_yes   = "";
	$monitor_no    = "checked";
}

//We check the value of Debug
if (trim($settings['priorityevents']) == "yes") {
	$priorityevents_yes   = "checked";
	$priorityevents_no    = "";
}
else {
	$priorityevents_yes   = "";
	$priorityevents_no    = "checked";
}


//We check the value of Record silence
if (trim($settings['recordsilence']) == "yes") {
	$recordsilence_yes = "checked";
	$recordsilence_no = "";
} else {
	$recordsilence_yes = "";
	$recordsilence_no = "checked";
}

//We check the value of Speech Beeps
if (trim($settings['speechbeeps']) == "yes") {
	$speechbeeps_yes = "checked";
	$speechbeeps_no = "";
} else {
	$speechbeeps_yes = "";
	$speechbeeps_no = "checked";
}

// Set logs config
// Get config
/*$logsvoximal_yes = "";
$logsvxml_yes    = "";
$logsapache_yes  = "";
$logsvoximal_no  = "checked";
$logsvxml_no     = "checked";
$logsapache_no   = "checked";
if (!empty($settings["logsapache"]))  {$logsapache_yes  = "checked"; $logsapache_no = "";}
if (!empty($settings["logsvoximal"])) {$logsvoximal_yes = "checked"; $logsvoximal_no = "";}
if (!empty($settings["logsvxml"]))    {$logsvxml_yes    = "checked"; $logsvxml_no    = "";}
*/

$tabindex = 0;
?>
<form autocomplete="off" name="settings" id="settings" action="config.php?display=voximalsettings&view=general" method="post">
	<input type="hidden" id="form" name="form" value="editsettings">
	<table width="700px">
		<tr>
			<td><h5><?php echo _("General");?><hr></h5></td>
		</tr>
		<tr>
			<td>
				<table width="600px">
                    <?php if (isset($version_voximal)) { ?>
					<tr>
						<td style="width: 27%;"><?php echo _("Version");?></td>
                        <td><?php echo $version_voximal; ?></td>
                    </tr>
                    <?php } ?>

					<tr>
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Dial format");?><span><?php echo _("Dial expression format for the transfers.");?></span></a></td>
						<td><input type="text" id="dialformat" name="dialformat" style="width: 140px;" value="<?php echo isset($settings['dialformat']) ? $settings['dialformat'] : 'SIP/%s' ?>" tabindex="<?php echo $tabindex++?>"></td>
						<td><span style="color: red" id="errDialFormat"></span></td>
					</tr>
					<tr>
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Blind application");?><span><?php echo _("Application to use for the blind transfers.");?></span></a></td>
						<td><input type="text" id="blindapplication" name="blindapplication" style="width: 140px;" value="<?php echo isset($settings['blindapplication']) ? $settings['blindapplication'] : '' ?>" tabindex="<?php echo $tabindex++?>"></td>
						<td><span style="color: red" id="errDialFormat"></span></td>
					</tr>
					<tr>
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Record silence");?><span><?php echo _("Save silence stream in record file.");?></span></a></td>
						<td>
							<table style="width: 100%; height: 20px;">
		        				<tbody>
		        					<tr>
		          						<td>
											<span class="radioset">
		            							<input id="recordsilence_yes" type="radio" name="recordsilence" value="yes" <?php echo $recordsilence_yes?> tabindex="<?php echo $tabindex++?>">
		            								<label for="recordsilence_yes">Yes</label>
		            							<input id="recordsilence_no" type="radio" name="recordsilence" value="no" <?php echo $recordsilence_no?> tabindex="<?php echo $tabindex++?>">
		            								<label for="recordsilence_no">No</label>
											</span>
		          						</td>
		        					</tr>
		      					</tbody>
	      					</table>
						</td>
					</tr>
					<tr>
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Threshold");?><span><?php echo _("Sensibility level for record/speech.");?></span></a></td>
						<td><input type="text" id="threshold" name="threshold" style="width: 140px;" value="<?php echo isset($settings['threshold']) ? $settings['threshold'] : '256' ?>" tabindex="<?php echo $tabindex++?>"></td>
						<td><span style="color: red" id="errThreshold"></span></td>
					</tr>					
					<tr>
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("WAV default codec");?><span><?php echo _("Codec type to use for record.");?></span></a></td>
						<td>
							<table style="width: 100%; height: 20px;">
		        				<tbody>
		        					<tr>
		          						<td>
											<span class="radioset">
		            							<input id="wavcodec_pcm" type="radio" name="wavdefaultcodec" value="pcm" <?php echo $wavcodec_pcm?> tabindex="<?php echo $tabindex++?>">
		            								<label for="wavcodec_pcm">pcm</label>
		            							<input id="wavcodec_gsm" type="radio" name="wavdefaultcodec" value="gsm" <?php echo $wavcodec_gsm?> tabindex="<?php echo $tabindex++?>">
		            								<label for="wavcodec_gsm">gsm</label>
											</span>
		          						</td>
		        					</tr>
		      					</tbody>
	      					</table>
						</td>
					</tr>
					<tr>
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Speech beeps");?><span><?php echo _("Play start and stop beeps to mark the speech recognition.");?></span></a></td>
						<td>
							<table style="width: 100%; height: 20px;">
		        				<tbody>
		        					<tr>
		          						<td>
											<span class="radioset">
		            							<input id="speechbeeps_yes" type="radio" name="speechbeeps" value="yes" <?php echo $speechbeeps_yes?> tabindex="<?php echo $tabindex++?>">
		            								<label for="speechbeeps_yes">Yes</label>
		            							<input id="speechbeeps_no" type="radio" name="speechbeeps" value="no" <?php echo $speechbeeps_no?> tabindex="<?php echo $tabindex++?>">
		            								<label for="speechbeeps_no">No</label>
											</span>
		          						</td>
		        					</tr>
		      					</tbody>
	      					</table>
						</td>
					</tr>
					<tr>
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Speech provider");?><span><?php echo _("If you use uniMRCP, you can define the default profile. (example :unimrcp:nua1)");?></span></a></td>
						<td><input type="text" id="speechprovider" name="speechprovider" style="width: 140px;" value="<?php echo isset($settings['speechprovider']) ? $settings['speechprovider'] : '' ?>" tabindex="<?php echo $tabindex++?>"></td>
						<td><span style="color: red" id="errSpeechProvider"></span></td>
					</tr>
					<tr>
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Debug");?><span><?php echo _("Voximal logs level.");?></span></a></td>
						<td>
							<table style="width: 100%; height: 20px;">
		        				<tbody>
		        					<tr>
		          						<td>
											<span class="radioset">
		            							<input type="radio" name="debug" id="debug_yes" value="yes" <?php echo $debug_yes?> tabindex="<?php echo $tabindex++?>">
		            								<label for="debug_yes">Yes</label>
		            							<input type="radio" name="debug" id="debug_no" value="no" <?php echo $debug_no?> tabindex="<?php echo $tabindex++?>">
		            								<label for="debug_no">No</label>
		            							<input type="radio" name="debug" id="debug_debug" value="interpreter" <?php echo $debug_debug?> tabindex="<?php echo $tabindex++?>">
		            								<label for="debug_debug">Interpreter</label>
											</span>
		          						</td>
		        					</tr>
		      					</tbody>
	      					</table>
						</td>
					</tr>
					<tr>
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Monitoring");?><span><?php echo _("Record call (bidirectionnal stream).<br><br><b><u>Warning:</u> If you active this option, you need to inform user that his call is recorded!</b>");?></span></a></td>
						<td>
							<table style="width: 100%; height: 20px;">
		        				<tbody>
		        					<tr>
		          						<td>
											<span class="radioset">
		            							<input type="radio" name="monitor" id="monitor_yes" value="yes" <?php echo $monitor_yes?> tabindex="<?php echo $tabindex++?>">
		            								<label for="monitor_yes">Yes</label>
		            							<input type="radio" name="monitor" id="monitor_no" value="no" <?php echo $monitor_no?> tabindex="<?php echo $tabindex++?>">
		            								<label for="monitor_no">No</label>
											</span>
		          						</td>
		        					</tr>
		      					</tbody>
	      					</table>
						</td>
					</tr>
					<tr>
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Priority events");?><span><?php echo _("Record call (bidirectionnal stream).<br><br><b><u>Warning:</u> If you active this option, you need to inform user that his call is recorded!</b>");?></span></a></td>
						<td>
							<table style="width: 100%; height: 20px;">
		        				<tbody>
		        					<tr>
		          						<td>
											<span class="radioset">
		            							<input type="radio" name="priorityevents" id="priorityevents_yes" value="yes" <?php echo $priorityevents_yes?> tabindex="<?php echo $tabindex++?>">
		            								<label for="priorityevents_yes">Yes</label>
		            							<input type="radio" name="priorityevents" id="priorityevents_no" value="no" <?php echo $priorityevents_no?> tabindex="<?php echo $tabindex++?>">
		            								<label for="priorityevents_no">No</label>
											</span>
		          						</td>
		        					</tr>
		      					</tbody>
	      					</table>
						</td>
					</tr>
					<tr>
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Cache timeout (s)");?><span><?php echo _("Define cache size in second.");?></span></a></td>
						<td><input type="text" id="cachetimeout" name="cachetimeout" style="width: 140px;" value="<?php echo isset($settings['cachetimeout']) ? $settings['cachetimeout'] : '60' ?>" tabindex="<?php echo $tabindex++?>"></td>
						<td><span style="color: red;" id="errCacheTimeout"></span></td>
					</tr>					
				</table>
			</td>
		</tr>
    <tr>
			<td><br><hr></td>
		</tr>		
		<tr>
			<td>
				<table>
					<tr>
						<td><input type="button" style="width: 80px; height: 25px;" onclick="save();" value="<?php echo _("Save");?>"></form></td>
						<td><input type="button" style="width: 80px; height: 25px;" onclick="location.reload();" value="<?php echo _("Discard");?>"></td>
					</tr>
				</table>
			</td>
		</tr>					
	</table>

<script type="text/javascript">

	function save() {		

		document.getElementById("errThreshold").innerHTML = "";
		document.getElementById("errDialFormat").innerHTML = "";
		document.getElementById("errCacheTimeout").innerHTML = "";

		var form = document.getElementById("settings");
		var dialformat = form.elements["dialformat"].value;
		var threshold = form.elements["threshold"].value;
		var cachetimeout = form.elements["cachetimeout"].value;

		var sub_threshold = 0; var sub_cachetimeout = 0;

		if (dialformat == '') {
			document.getElementById("errDialFormat").innerHTML = "  The value must be a type/%s@peer.";
		} else {
  		sub_dialformat = 1;
		}

		if (isNaN(threshold)) {
			document.getElementById("errThreshold").innerHTML = "  The value must be a number.";
		} else {
			if (!isInt(threshold)) {
				document.getElementById("errThreshold").innerHTML = "  The value must be an integer.";
			} else {
				if ((threshold < 1) || (threshold > 32767)) {
					document.getElementById("errThreshold").innerHTML = "  The value is not in the correct range [1...32767]";
				} else {
					sub_threshold = 1;
				}
			}
		}

		if (isNaN(cachetimeout)) {
			document.getElementById("errCacheTimeout").innerHTML = "  The value must be a number.";
		} else {
			if (!isInt(cachetimeout)) {
				document.getElementById("errCacheTimeout").innerHTML = "  The value must be an integer.";
			} else {
				if (cachetimeout < -1) {
					document.getElementById("errCacheTimeout").innerHTML = "  The value is not in the correct range [-1,0...]";
				} else {
					sub_cachetimeout = 1;
				}
			}
		}

		if (sub_dialformat==1 && sub_threshold == 1 && sub_cachetimeout == 1) {
			form.submit();
		}

	}

	function isInt(n) {
		return n % 1 === 0;
	}

</script>