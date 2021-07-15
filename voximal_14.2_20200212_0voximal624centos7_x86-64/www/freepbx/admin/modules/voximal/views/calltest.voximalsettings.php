<?php 
// File    : call.voximalsettings.php
// Version : $Revision: 1.11 $


global $astman;

if (isset($_POST['form'])) {
    if ($_POST['iaxport'] != $_POST['prevport']) {
        // Update iaxport
    	updateIAXConf($_POST['iaxport']);
	    needreload();
    }
    if ($_POST['iaxport'] != $_POST['prevport'] ||
        $_POST['mobilephone'] != $_POST['prevnumber']) {
        setCallParams($_POST['iaxport'], $_POST['mobilephone']) ;
    }
}

// Get command voximal show license
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
}


// Get the Test Identification Number
if (isset($license_infos["PIN"])) {
    $PIN = $license_infos["PIN"];
}
else {
    $PIN = "0000";
}
if (isset($license_infos["iaxport"])) {
    $iaxport = $license_infos["iaxport"];
}
else {
    $iaxport = "4569";
}
if (isset($license_infos["phonenumber"])) {
    $phonenumber = $license_infos["phonenumber"];
}
else {
    $phonenumber = "";
}

// Check iax port in iax_custom.conf file
//  [general]
//  port=5036
/*$iaxport     = "4569";
$phonenumber = "";
$out = shell_exec("grep port /etc/asterisk/iax_general_custom.conf | cut -d'=' -f 2");
$out = str_replace("\n","",rtrim(ltrim($out)));
if (!empty($out) && $out != "")
    $iaxport = $out;
$out = shell_exec("grep phonenumber /var/opt/voximal/testcall.conf | cut -d'=' -f 2");
$out = str_replace("\n","",rtrim(ltrim($out)));
if (!empty($out) && $out != "" )
    $phonenumber = $out;
*/
?>

<style TYPE = "text/css">
.showtable, .showtable TD, .showtable TH
{
font-family: verdana;
font-size: 10pt;
line-height: 0.5;
}
</style>

<br>
<span style="font-family: Helvetica">
<?php echo _("An easy way to make tests.")?><br>
<br>
<?php echo _("To call your Voxibot :")?><br>
<?php echo _("Call the <a href='tel:0033972538823'>+33(0)972538823</a> and enter the PIN number.")?><br>
</span>
<br>
<form autocomplete="off" name="license" action="config.php?display=voximalsettings&view=calltest" method="post">
	<input type="hidden" id="form" name="form" value="form">
	<input type="hidden" id="prevport" name="prevport" value="<?php echo $iaxport?>">
	<input type="hidden" id="prevnumber" name="prevnumber" value="<?php echo $phonenumber?>">
	<table width="700px">
   			<tr>
				<td style="width: 30%;"><a href="#" class="info">PIN<span><?php echo _("This is your Test Identification Number. This PIN is used to access this IVR when you call the Voximal Test Phone Number +33XXXXXXX.")?></span></a></td>
				<td style="width: 70%;"><b><?php echo $PIN;?></b></td>
			</tr>
   			<tr>
				<td style="width: 30%;"><a href="#" class="info"><?php echo _("Port number")?><span><?php echo _("The UDP port number to use to access to the Call Test Interface (you need to enable this UDP port number for external internet access). <br>Default value: 4569")?></span></a></td>
				<td style="width: 70%;"><?php echo $iaxport?></td>
				<?php /* <td style="width: 70%;"><input type="number" id="iaxport" name="iaxport" style="width: 80px;" value="<?php echo $iaxport->" onChange="confirmPort();"></td> */ ?>
			</tr>
            <?php if ($phonenumber != "") { ?>
   			<tr>
				<td style="width: 30%;"><a href="#" class="info"><?php echo _("Phone number")?><span><?php echo _("Enter your phone number where you'll make call test.<br>If you set this number, it'll not necessary to enter the PIN.<br>Not mandatory")?></span></a></td>
				<td style="width: 70%;"><?php echo $phonenumber?></td>
				<?php /* <td style="width: 70%;"><input type="number" id="mobilephone" name="mobilephone" style="width: 300px;" value="<?php echo $phonenumber->"></td> */ ?>
			</tr>
            <?php } ?>
            <?php /*
   			<tr>
				<td>
					<br>
					<table id="tab_buttons">
						<tr>
							<td><input type="submit" style="width: 80px; height: 25px;" value="<?php echo _("Save");->"></form></td>
							<td><input type="button" style="width: 80px; height: 25px;" value="<?php echo _("Discard");->"></td>
						</tr>
					</table>
					<br>
				</td>
			</tr>
             */ ?>

	</table>
</form>


<script type="text/javascript">
function confirmPort(){
    var port = document.getElementById("iaxport");
    var answer = confirm ("<?php echo _("Are you sure you want to set")?> "+port.value+" <?php echo _("for port")?> ?");
    if (!answer)
        port.value = <?php echo $iaxport?>;
}
</script>
