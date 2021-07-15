<?php
// File    : accounts.voximalapp.php
// Version : $Revision: 1.2 $


$edit=0;

//print_r($_REQUEST);

/*
 * If opened by popup => $_REQUEST contain $_REQUEST["fw_popover"]
 */

//print_r($_POST);
if (isset($_POST['form'])) {
	if($_POST['form'] == "add") {
		$voximal = array(
				"name" => $_POST['name'],
				"url" => $_POST['url'],
				"maxsessions" => $_POST['maxsessions'],
				"dialformat" => $_POST['dialformat'],
				"mark" => $_POST['mark'],
				"speech" => $_POST['speech'],
				"speechprovider" => $_POST['speechprovider'],
				"startdelay" => $_POST["startdelay"] ,
				"vxmlparam"  => $_POST["vxmlparam"] ,
				"maxtime"    => $_POST["maxtime"]
		);
		addVoximal($voximal);
	} elseif ($_POST['form'] == "edit") {
		$modifiedvoximal = array(
				"name" => $_POST['name'],
				"url" => $_POST['url'],
				"maxsessions" => $_POST['maxsessions'],
				"dialformat" => $_POST['dialformat'],
				"mark" => $_POST['mark'],
				"speech" => $_POST['speech'],
				"speechprovider" => $_POST['speechprovider'],
				"startdelay" => $_POST["startdelay"] ,
				"vxmlparam"  => $_POST["vxmlparam"] ,
				"maxtime"    => $_POST["maxtime"]
		);
		updateVoximal($_POST['prevname'],$modifiedvoximal);
	} elseif ($_POST['form'] == "delete") {
		deleteVoximal($_POST['name']);
	}
    needreload();
} else {
	if (isset($_GET['action']) && $_GET['action'] == "edit") {
		$edit = 1;
		$voximal = getVoximal($_GET['vxml']);
		$editvoximal = $voximal[0];
		$speech = $editvoximal['speech'];
		if ($speech == "yes") {
			$speech_checked_yes = "checked";
			$speech_checked_emulation = "";
			$speech_checked_automatic = "";
			$speech_checked_debug = "";
			$speech_checked_no = "";
		} elseif ($speech == "emulation") {
			$speech_checked_yes = "";
			$speech_checked_emulation = "checked";
			$speech_checked_automatic = "";
			$speech_checked_debug = "";
			$speech_checked_no = "";
		} elseif ($speech == "automatic") {
			$speech_checked_yes = "";
			$speech_checked_emulation = "";
			$speech_checked_automatic = "checked";
			$speech_checked_debug = "";
			$speech_checked_no = "";
		} elseif ($speech == "debug") {
			$speech_checked_yes = "";
			$speech_checked_emulation = "";
			$speech_checked_automatic = "";
			$speech_checked_debug = "checked";
			$speech_checked_no = "";
		} elseif ($speech == "no") {
			$speech_checked_yes = "";
			$speech_checked_emulation = "";
			$speech_checked_automatic = "";
			$speech_checked_debug = "";
			$speech_checked_no = "checked";
		} else {
			$speech_checked_yes = "";
			$speech_checked_emulation = "checked";
			$speech_checked_automatic = "";
			$speech_checked_debug = "";
			$speech_checked_no = "";

		}
	}
}

$names = getVoximalList("name");
$nameslist = "";
foreach ($names as $name) {
	$nameslist = $nameslist." ".$name['name'];
}

$services = getVoximalServices();

// Get selected file
if ($edit && isset($editvoximal['url'])) {
   $pos = strpos($editvoximal['url'], "/vxml");
   if ($pos) {
      $selectedFile = substr($editvoximal['url'], $pos+6);
      error_log("selectFile = $selectedFile");
   }
}

// Do not display navigation on popup
if (!isset($_REQUEST["fw_popover"]))
    echo load_view(dirname(__FILE__) . '/rnav.voximalapp.php',array('names' => $names));

?>
<form autocomplete="off" name="general" id="general" action="config.php?display=voximalapp" method="post">
    <input type="hidden" id="view" name="view" value="accounts">
	<?php if ($edit) {?>
		<input type="hidden" id="form" name="form" value="edit">
		<input type="hidden" id="prevname" name="prevname" value="<?php echo $editvoximal['name']?>">
	<?php } else {?>
		<input type="hidden" id="form" name="form" value="add">
	<?php }?>
	<input type="hidden" id="nameslist" name="nameslist" value="<?php echo $nameslist ?>">
	<table style="width: 670px;">
		<tr >
			<td colspan="2"><h3><?php if ($edit) echo _("Edit"); else echo _("Add");?> Application<hr></h3></td>
		</tr>
		<tr>
			<td colspan="2"><i><?php  echo _("The fields marked with * can not be left in blank")?>.</i><br><br></td>
		</tr>
		<tr>
			<td style="width: 25%;"><a href="#" class="info"><?php echo _("Name")?>*<span><?php echo _("Name for this application")?>.</span></a></td>
			<td style="width: 75%;"><input type="text" name="name" id="name" style="width: 250px;" value="<?php echo (isset($editvoximal) && $editvoximal['name']) ? $editvoximal['name'] : ""?>"><span id="errName" style="color: red"></span></td>
		</tr>
		<tr>
			<td style="width: 25%;"><a href="#" class="info">URL*<span><?php echo _("{VoiceXML URL} This function indicates the VoiceXML URL of the account.")?></span></a></td>
			<td style="width: 75%;"><input type="text" id="url" name="url" style="width: 250px;" value="<?php echo (isset($editvoximal) && $editvoximal['url']) ? $editvoximal['url'] : ""?>"><span id="errURL" style="color: red"></span>
            <select name="selvoximalsrv" id="selVoximalSrv" onChange="setUrl();" style="width: 150px;" >
                <?php
                echo '<option value="toselect" SELECTED >'._("Select").' ...</option>';
                if ($services)
                foreach ($services as $k => $file)    {
                    if ($edit && isset($selectedFile) &&  $file==$selectedFile)
                        echo '<option value="'.$file.'" SELECTED >'.$file.'</option>';
                    else
                        echo '<option value="'.$file.'" >'.$file.'</option>';
                }?>
			</select></td>
		</tr>
		<tr>
			<td style="width: 25%;"><a href="#" class="info"><?php echo _("Max Sessions")?><span>
				<?php echo _("{0...120} This indicates the maximum number of sessions allowed to this account. If there are not enough
				sessions then the VoiceXML application will generate an error.")?>
			</span></a></td>
			<td style="width: 75%;"><input type="text" id="maxsessions" name="maxsessions" style="width: 250px;" value="<?php echo (isset($editvoximal) && $editvoximal['maxsessions']) ? $editvoximal['maxsessions'] : ""?>"><span id="errSessions" style="color: red"></span></td>
		</tr>
		<tr>
			<td style="width: 25%;"><a href="#" class="info"><?php echo _("Dial Format")?><span>
				<?php echo _('{application(]/%s[)} This is a string to specify the interface and the peer that has been chosen for the transfer.
				The "%s" will be replaced by the string set in the <transfer> dest attribute. Remember to prefix the dest value
				with "tel:" to generate the transfer function. Other prefixes have been added to match some of the Asterisk functions,
				such as conference, call an application, etc. The default value is SIP/%s. This is similar to the general function,
				but for the account only. If not set, use the general value.')?>
			</span></a></td>
			<td style="width: 75%;"><input type="text" id="dialformat" name="dialformat" style="width: 250px;" value="<?php echo (isset($editvoximal) && $editvoximal['dialformat']) ? $editvoximal['dialformat'] : ""?>"></td>
		</tr>
		<tr>
			<td style="width: 25%;"><a href="#" class="info"><?php echo _("Mark")?><span>
				<?php echo _("Add a mark in traces, to help you to follow a call. This mark will be add to the channel number column in traces, with the session ID (Example : ...|33|... &rarr; ...|33_1_user1|... ).<br>
                You can set a specific string or predifine variables :<br>
                &nbsp;&nbsp;<b>@remote</b> : caller number<br>
				&nbsp;&nbsp;<b>@local</b> : called number <br>
				&nbsp;&nbsp;<b>@id</b> : VoieXML id parameter value<br>
				&nbsp;&nbsp;<b>@param</b> : VoiceXML parameter value<br>")?>
			</span></a></td>
			<td style="width: 75%;"><input type="text" id="mark" name="mark" style="width: 250px;" value="<?php echo (isset($editvoximal) && $editvoximal['mark']) ? $editvoximal['mark'] : ""?>"></td>
		</tr>
		<tr>
			<td style="width: 25%;"><a href="#" class="info"><?php echo _("Speech")?><span>
				<?php echo _("Speech recognition activation to connect an ASR engine.
				This speech function is as the general function, but for
				the account only. If not set, use the general value.")?>
			</span></a></td>
			<td style="width: 75%;">
				<table width="100%">
        			<tbody>
        				<tr>
          					<td>
								<span class="radioset ui-buttonset">
           							<input id="speech-emulation" type="radio" name="speech" id="speech" <?php echo isset($speech_checked_emulation) ? $speech_checked_emulation : 'checked'; ?> value="emulation" class="ui-helper-hidden-accessible">
           							<label for="speech-emulation" class="ui-button ui-widget ui-button-text-only" role="button"><span style="padding: .1em .5em;" class="ui-button-text">Emulation</span></label>
           							<input id="speech-no" type="radio" name="speech" id="speech" <?php echo isset($speech_checked_no) ? $speech_checked_no : '?'; ?> value="no" class="ui-helper-hidden-accessible">
           							<label for="speech-no" class="ui-button ui-widget ui-button-text-only ui-corner-right" role="button"><span style="padding: .1em .5em;" class="ui-button-text">No</span></label>
            						<input id="speech-yes" type="radio" name="speech" id="speech" <?php echo isset($speech_checked_yes) ? $speech_checked_yes : '?'; ?> value="yes" class="ui-helper-hidden-accessible">
           							<label for="speech-yes" class="ui-button ui-widget ui-button-text-only ui-corner-left" role="button"><span style="padding: .1em .5em;" class="ui-button-text">Yes</span></label>
           							<input id="speech-automatic" type="radio" name="speech" id="speech" <?php echo isset($speech_checked_automatic) ? $speech_checked_automatic : '?'; ?> value="automatic" class="ui-helper-hidden-accessible">
           							<label for="speech-automatic" class="ui-button ui-widget ui-button-text-only" role="button"><span style="padding: .1em .5em;" class="ui-button-text">Automatic</span></label>
           							<!-- <input id="speech-debug" type="radio" name="speech" id="speech" <?php echo isset($speech_checked_debug) ?  $speech_checked_debug : '?'; ?> value="debug" class="ui-helper-hidden-accessible"> -->
           							<!-- <label for="speech-debug" class="ui-button ui-widget ui-button-text-only" role="button"><span style="padding: .1em .5em;" class="ui-button-text">Debug</span></label> -->
								</span>
       						</td>
       					</tr>
    				</tbody>
    			</table>
			</td>
		</tr>
    <tr>
			<td style="width: 25%;"><a href="#" class="info">Speech Provider<span>
				{nuance/voicebox/lumenvox/verbio} You can set which speech recognition provider to allocate to the speech resource.
				When the default is empty, use the first option.This speech function is as the general function,
				but for the account only. If not set, use the general value.
			</span></a></td>
			<td style="width: 75%;"><input type="text" id="speechprovider" name="speechprovider" style="width: 250px;" value="<?php echo (isset($editvoximal) && $editvoximal['speechprovider']) ? $editvoximal['speechprovider'] : ""?>"><span id="errProvider" style="color: red"></span></td>
		</tr>
		<tr>
			<td style="width: 25%;"><a href="#" class="info"><?php echo _("Max time (s)")?><span>
            <?php echo _("Set the maximum duration of call in secondes.<br>The default value is <b>0</b>, which means <b>unlimited</b>.")?>
			</span></a></td>
			<td style="width: 75%;"><input type="number" id="maxtime" name="maxtime" style="width: 250px;" value="<?php echo (isset($editvoximal) && $editvoximal['maxtime']) ? $editvoximal['maxtime'] : ""?>"></td>
		</tr>
		<tr>
			<td style="width: 25%;"><a href="#" class="info"><?php echo _("Session parameter")?><span>
            String to pass to the VoiceXML service as a parameter.
			</span></a></td>
			<td style="width: 75%;"><input type="text" id="vxmlparam" name="vxmlparam" style="width: 250px;" value="<?php echo (isset($editvoximal) && $editvoximal['vxmlparam']) ? $editvoximal['vxmlparam'] : ""?>"></td>
		</tr>
		<tr>
			<td style="width: 25%;"><a href="#" class="info"><?php echo _("Start delay (ms)")?><span>
            Delay in milli secondes to start the service.<br>
            The default value is <b>0</b>, which means <b>no delay</b>.
			</span></a></td>
			<td style="width: 75%;"><input type="number" id="startdelay" name="startdelay" style="width: 250px;" value="<?php echo (isset($editvoximal) && $editvoximal['startdelay']) ? $editvoximal['startdelay'] : "2000"?>"></td>
		</tr>
		<tr>
			<td><span id="errGoto0" style="color: red"></span></td>
			<td></td>
		</tr>
		<tr>
			<td colspan="2">
				<br>
				<table>
					<tr>
						<td><input type="button" onclick="<?php echo $edit ? "modify()" : "create()"?>;" value="<?php echo $edit ? _("Save Changes") : _("Create")?>"></form></td>
						<td>
						<?php if ($edit) {?>
							<form autocomplete="off" name="general" id="general" action="config.php?display=voximalapp" method="post">
								<input type="hidden" id="view" name="view" value="accounts">
								<input type="hidden" id="form" name="form" value="delete">
								<input type="hidden" id="name" name="name" value="<?php echo $editvoximal['name']?>">
								<input type="submit" value="<?php echo _("Delete")?>">
							</form>
						<?php }?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

<?php if ($edit) {?>
<script type="text/javascript">

	function modify() {

		document.getElementById("errName").innerHTML = "";
		//document.getElementById("errGoto0").innerHTML = "";
		document.getElementById("errURL").innerHTML = "";
		document.getElementById("errSessions").innerHTML = "";

		var form = document.getElementById("general");
		var name = form.elements['name'].value;
		var prevname = form.elements['prevname'].value;
		var nameslist = form.elements['nameslist'].value;
		var url = form.elements["url"].value;
		var maxsessions = form.elements["maxsessions"].value;

		var sub_name = 0; var sub_url = 0; var sub_max = 0;

		if (name == "" || name == null) {
			document.getElementById("errName").innerHTML = "  It must be specified";
		} else {
			if (name.length > 100) {
				document.getElementById("errName").innerHTML = "  The max length is 100 characters";
			} else if (name.indexOf(' ') >= 0) {
				document.getElementById("errName").innerHTML = "  It can not contain spaces.";
			} else {
				var listofnames = nameslist.split(" ");
				var exist = 0;
				for (var i = 0; i < listofnames.length; i++) {
					if (name == listofnames[i]) {
						exist = 1;
						break;
					}
				}
				if (exist == 1 && name != prevname) {
					document.getElementById("errName").innerHTML = "  This name already exist";
				} else {
					sub_name = 1;
				}
			}
		}

		if (url == null || url == "") {
			document.getElementById("errURL").innerHTML = "  It must be specified.";
		} else {
			sub_url = 1;
		}

		if (maxsessions == null || maxsessions == "") {
			sub_max = 1;
		} else if (isNaN(maxsessions)) {
			document.getElementById("errSessions").innerHTML = "  It can not contain characters.";
		} else {
			sub_max = 1;
		}

		if (sub_name == 1 && sub_url == 1 && sub_max == 1) {
			alert("Application modified correctly");
			form.submit();
		}
	}
    function setUrl() {
        var selectUrl = document.getElementById("selVoximalSrv");
        var urlSelected = selectUrl.options[selectUrl.selectedIndex].value;
        if (urlSelected == "toselect")
            document.getElementById("url").value = "";
        else
            document.getElementById("url").value = "http://localhost/vxml/"+urlSelected;
    }

</script>
<?php } else {?>
<script type="text/javascript">

	function create() {

		document.getElementById("errName").innerHTML = "";
		//document.getElementById("errGoto0").innerHTML = "";
		document.getElementById("errURL").innerHTML = "";
		document.getElementById("errSessions").innerHTML = "";

		var form = document.getElementById("general");
		var name = form.elements['name'].value;
		var nameslist = form.elements['nameslist'].value;
		var url = form.elements["url"].value;
		var maxsessions = form.elements["maxsessions"].value;

		var sub_name = 0; var sub_url = 0; var sub_max = 0;

		if (name == "" || name == null) {
			document.getElementById("errName").innerHTML = "  It must be specified";
		} else {
			if (name.length > 100) {
				document.getElementById("errName").innerHTML = "  The max length is 100 characters";
			} else if (name.indexOf(' ') >= 0) {
				document.getElementById("errName").innerHTML = "  It can not contain spaces.";
			} else {
				var listofnames = nameslist.split(" ");
				var exist = 0;
				for (var i = 0; i < listofnames.length; i++) {
					if (name == listofnames[i]) {
						exist = 1;
						break;
					}
				}
				if (exist == 1) {
					document.getElementById("errName").innerHTML = "  This name already exist";
				} else {
					sub_name = 1;
				}
			}
		}

		if (url == null || url == "") {
			document.getElementById("errURL").innerHTML = "  It must be specified.";
		} else {
			sub_url = 1;
		}

		if (maxsessions == null || maxsessions == "") {
			sub_max = 1;
		} else if (isNaN(maxsessions)) {
			document.getElementById("errSessions").innerHTML = "  It can not contain characters.";
		} else {
			sub_max = 1;
		}

		if (sub_name == 1 && sub_url == 1 && sub_max == 1) {
			alert("Application added correctly");
			form.submit();
		}
	}
    function setUrl() {
        var selectUrl = document.getElementById("selVoximalSrv");
        var urlSelected = selectUrl.options[selectUrl.selectedIndex].value;
        if (urlSelected == "toselect")
            document.getElementById("url").value = "";
        else
            document.getElementById("url").value = "http://localhost/vxml/"+urlSelected;
    }

</script>


<?php }?>