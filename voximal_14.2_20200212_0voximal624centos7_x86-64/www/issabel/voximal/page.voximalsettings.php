<?php
//Check if user is "logged in"
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

//This is the function for the "Restart Interpreter" button
if (isset($_POST['action']) && $_POST['action'] == "restart") {

	manageInterpreter("restart");
	$asterisk = shell_exec("service asterisk status");
	
	$msg = "";
	
	if (strpos($asterisk,"running") === false) {
		$msg = $msg."There has been a problem and the telephony has stopped working. <br>";
	}
	
	if (empty($msg)) {
		$msg = "OK";
	} else {
		$msg = $msg."Try to restart again the asterisk to check if it can be solved.";
	}
	
	echo json_encode(array('result' => $msg));
	exit();
}

$viewGeneralClass  = 'class="active"';
$viewTtsClass  = '';
$viewAsrClass  = '';
$viewLicenseClass  = '';
$viewCallTestClass = '';
if (isset($_GET['view']) && $_GET['view'] == "tts"){
    $viewTtsClass = 'class="active"';
    $viewGeneralClass = '';
}
else if (isset($_GET['view']) && $_GET['view'] == "asr"){
    $viewAsrClass = 'class="active"';
    $viewGeneralClass = '';
}
else if (isset($_GET['view']) && $_GET['view'] == "license"){
    $viewLicenseClass = 'class="active"';
    $viewGeneralClass = '';
}
else if (isset($_GET['view']) && $_GET['view'] == "calltest"){
    $viewCallTestClass = 'class="active"';
    $viewGeneralClass = '';
}

?>
<style TYPE = "text/css">
#current {
	text-decoration: underline;
}
ul#tabnav {
    list-style-type: none;
    padding-bottom: 24px;
    border-bottom: 1px solid #808080;
    margin: 0;
}
ul#tabnav li {
    float: left;
    height: 21px;
    background-color: #C2C2D6;
    margin: 2px 2px 0 2px;
    border: 1px solid #808080;
    height: 23px;
}
ul#tabnav li.active {
    border-bottom: 1px solid #fff;
    background-color: #fff;
}
ul#tabnav li.active a {
    color: #000;
}
#tabnav a {
    float: left;
    display: block;
    color: black;
    text-decoration: none;
    padding: 4px;
}
#tabnav a:hover {
    background: #fff;
}
</style>


<h2><?php echo _("Voximal Settings")?></h2>
<table width="700px">
		<tr>
			<!-- <td><br><h5>Categories: &nbsp;&nbsp;&nbsp;&nbsp;<span class="<?php echo $viewGeneralClass;?>"><a href="config.php?display=voximalsettings&view=general">General</a></span>&nbsp;&nbsp;<span class="<?php echo $viewLicenseClass;?>"><a href="config.php?display=voximalsettings&view=license">License</a></span></h5></td> -->
			<td>
                <ul id="tabnav">
                     <li <?php echo $viewGeneralClass;?>><a href="config.php?display=voximalsettings&view=general"><?php echo _("General");?></a></li>
                     <li <?php echo $viewTtsClass;?>><a href="config.php?display=voximalsettings&view=tts"><?php echo _("Synthesis");?></a></li>
                     <li <?php echo $viewAsrClass;?>><a href="config.php?display=voximalsettings&view=asr"><?php echo _("Recognition");?></a></li>
                     <li <?php echo $viewLicenseClass;?>><a href="config.php?display=voximalsettings&view=license"><?php echo _("License");?></a></li>
                     <li <?php echo $viewCallTestClass;?>><a href="config.php?display=voximalsettings&view=calltest"><?php echo _("Call Test");?></a></li>
                </ul>
             </td>
		</tr>
</table>
<?php 
	if ($viewLicenseClass != '') {
		echo load_view(dirname(__FILE__) . '/views/license.voximalsettings.php');
	}
	else if ($viewTtsClass != '') {
		echo load_view(dirname(__FILE__) . '/views/tts.voximalsettings.php');
	}
	else if ($viewAsrClass != '') {
		echo load_view(dirname(__FILE__) . '/views/asr.voximalsettings.php');
	}
	else if ($viewCallTestClass != '') {
		echo load_view(dirname(__FILE__) . '/views/calltest.voximalsettings.php');
	}
    else {
		echo load_view(dirname(__FILE__) . '/views/general.voximalsettings.php');
	}
?>

