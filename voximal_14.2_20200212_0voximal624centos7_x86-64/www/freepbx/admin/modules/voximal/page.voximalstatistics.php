<?php
// File    : page.voximalstatistics.php
// Version : $Revision: 1.10 $


//Check if user is "logged in"
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

$stats_infos['Sessions pending'] = "0";
$stats_infos['Sessions peak']    = "0";
$stats_infos['Average sessions'] = "0";


global $astman;
if ($astman) {
	$stats = $astman->send_request('Command',array('Command'=>"voximal show statistics"));
	$stats = explode("\n",$stats['data']);
	$dates = $astman->send_request('Command',array('Command'=>"voximal show dates"));
	$dates = explode("\n",$dates['data']);
	$version = $astman->send_request('Command',array('Command'=>"voximal show version"));
	$version = explode("\n",$version['data']);
	$license = $astman->send_request('Command',array('Command'=>"voximal show license"));
	$license = explode("\n",$license['data']);

    // Get voximal version
    foreach ($version as $line) {
        list($key, $val) = explode(":", $line);
        if (strcasecmp(rtrim(ltrim($key)), "Version") == 0) {
            $version_voximal = $val;
            break;
        }
    }
    // Get max sessions and config
    foreach ($license as $line) {
        if (strpos($line,":") !== false) {
            $value = explode(":", $line);
            if (count($value) == 1)
            $value[1]="";
            list($key, $val) = $value;
            $license_infos[rtrim(ltrim($key))] = trim(ltrim($val));
        }
    }
    // Get stats
    foreach ($stats as $line) {
        //freepbx_log(FPBX_LOG_CRITICAL,"==== DBGJYG: vxmlstats $line");
        if (strpos($line,":") !== false) {
            $value = explode(":", $line);
            if (count($value) == 1)
            $value[1]="";
            list($key, $val) = $value;
            $stats_infos[rtrim(ltrim($key))] = trim(ltrim($val));
        }
    }
    // Get dates
    foreach ($dates as $line) {
        if (strpos($line,":") !== false) {
            //freepbx_log(FPBX_LOG_CRITICAL,"==== DBGJYG: vxmlstats dates $line");
            $value = explode(":", $line);
            if (count($value) == 1)
            $value[1]="";
            list($key, $val) = $value;
            $dates_infos[rtrim(ltrim($key))] = trim(ltrim($val));
        }
    }
}

$color_high   = "#FE3210";
$color_medium = "#FE801E";
$color_low    = "#FFE856";

$max_sessions = $license_infos["Max sessions"];

$per_pending = $stats_infos['Sessions pending'] * 100 / $max_sessions;
//if ($per_pending == 0)
//    $per_pending=1;
$color_pending = $color_medium;
if ($per_pending >= 80)
    $color_pending = $color_high;
else if ($per_pending <= 20)
    $color_pending = $color_low;

$per_peak   = $stats_infos['Sessions peak'] * 100 / $max_sessions;
$color_peak = $color_medium;
if ($per_peak >= 80)
    $color_peak = $color_high;
else if ($per_peak <= 20)
    $color_peak = $color_low;

$per_avgsess   = $stats_infos['Average sessions'] * 100 / $max_sessions;
$color_avgsess = $color_medium;
if ($per_avgsess >= 80)
    $color_avgsess = $color_high;
else if ($per_avgsess <= 20)
    $color_avgsess = $color_low;

?>


<h2><?php echo _("Voximal Statistics")?></h2>
<style TYPE = "text/css">
.tableresume {
    font-family: verdana;
    font-size: 10pt;
    line-height: 0.5;
    text-align: left;
}
.tableresume td:nth-child(1){
    width: 150px;
}

.showtable, .showtable TD, .showtable TH
{
font-family: verdana;
font-size: 10pt;
line-height: 0.5;
text-align: left;
}

#tableau dl {
		width:350px;
		left:150px;
		border:1px solid #000;
		font-weight:bold;
        font-size:10px;
		position:relative;
		margin:5px;
	}
	#tableau dt {
		border:1px solid #000;
		left:-155px;
		width:150px;
		background-color:#EEEEEE;
		position:absolute;
		color:#0D5CAB;
		margin-top:-1px;
	}
	#tableau dd {
		text-align:right;
		margin:0;
		color:#0D5CAB;
	}
	#pcts_pending dd {
		width:<?php echo $per_pending;?>%;
		background-color: <?php echo $color_pending;?>;
	}
	#pcts_peak dd {
		width:<?php echo $per_peak;?>%;
		background-color: <?php echo $color_peak;?>;
	}
	#pcts_avgsess dd {
		width:<?php echo $per_avgsess;?>%;
		background-color:<?php echo $color_avgsess;?>;
	}


.list{
	/*width:100%;*/
    width:350px;
    font-family: verdana;
    font-size: 12px;
	border-collapse:collapse;
	padding:2px;
    border:#4e95f4 1px solid;
    margin-left: 10px;
}

.list caption {
    margin: auto;
}

.list th
{
	padding:2px;
    border:#4e95f4 1px solid;
    font-size: 10px;
    letter-spacing: 1px;
    background: #dae5e0;
}

.list td{
    /*text-align: center;*/
	padding:4px;
    border:#4e95f4 1px solid;
/*    A {color: blue}*/
/*    A:link {text-decoration: none}*/
}
.list td:nth-child(1){
    min-width: 110px;
}
.list td:nth-child(2){
    min-width: 140px;
}
.list td:nth-child(3){
    min-width: 110px;
}
.list td:nth-child(4){
    min-width: 140px;
}

/* provide some minimal visual accomodation for IE8 and below */
.list tr{
	background: #b8d1f3;
}
/*  Define the background color for all the ODD background rows  */
.list tr:nth-child(odd){
	background: #b8d1f3;
}
/*  Define the background color for all the EVEN background rows  */
.list tr:nth-child(even){
	background: #dae5f4;
}

</style>
    <table class="tableresume">
        <tr>
			<td >Version</td>
            <td><?php echo $version_voximal; ?></td>
        </tr>
        <tr>
			<td ><?php echo _("Start module")?></td>
            <td><?php echo $dates_infos["Start module"]; ?></td>
        </tr>
        <tr>
			<td ><?php echo _("Reload configuration")?></td>
            <td><?php echo $dates_infos["Reload configuration"]; ?></td>
        </tr>
        <tr>
			<td ><?php echo _("Max sessions")?></td>
            <td><?php echo $max_sessions; ?></td>
        </tr>
    </table>
    <table class="showtable">
        <tr><td>
        <div id="tableau">
        	<dl id="pcts_pending">	<dt><?php echo _("Pending")?></dt>          <dd><?php echo $stats_infos['Sessions pending'];?></dd></dl>
        	<dl id="pcts_peak">	    <dt><?php echo _("Peak")?></dt> 	          <dd><?php echo $stats_infos['Sessions peak'];?></dd></dl>
        	<dl id="pcts_avgsess">	<dt><?php echo _("Average sessions")?></dt> <dd><?php echo $stats_infos['Average sessions'];?></dd></dl>
        </div>
        </td></tr>
    </table>

<h5>Sessions<hr></h5>
<!-- or type showtable -->
<table class="list">
        <tr>
			<td>Opened</td>
            <td><b><?php echo $stats_infos['Sessions opened'] ?></b></td>
			<td>Error</td>
            <td><span style="color: red"><b><?php echo $stats_infos['Sessions error'] ?></b></span></td>
        </tr>
        <tr>
			<td>Waiting</td>
            <td><b><?php echo $stats_infos['Sessions waiting'] ?></b></td>
			<td>Refused</td>
            <td><span style="color: red"><b><?php echo $stats_infos['Sessions refused'] ?></b></span></td>
        </tr>
        <tr>
			<td>Waited</td>
            <td><b><?php echo $stats_infos['Sessions waited'] ?></b></td>
			<td>Denied</td>
            <td><span style="color: red"><b><?php echo $stats_infos['Sessions denied'] ?></b></span></td>
        </tr>
        <tr>
			<td>Needed</td>
            <td><b><?php echo $stats_infos['Sessions needed'] ?></b></td>
			<td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
			<td>Max duration</td>
            <td><b><?php echo $stats_infos['Sessions maxduration'] ?></b>s</td>
			<td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
</table>
<h5>Connections<hr></h5>
<!-- or type  list -->
<table class="showtable">
  <tr>
	<td>Lost</td>
    <td><span style="color: red"><b><?php echo $stats_infos['Connections lost'] ?></b></span></td>
 	<td>Retrieve</td>
    <td><b><?php echo $stats_infos['Connections retrieve'] ?></b></td>
	<td>Error</td>
    <td><span style="color: red"><b><?php echo $stats_infos['Connections error'] ?></b></span></td>
  </tr>
</table>

<h5>Average<hr></h5>
<table class="list">
        <tr>
			<td>Duration</td>
            <td><b><?php echo $stats_infos['Average duration'] ?></b>s</td>
			<td>Speech</td>
            <td><b><?php echo $stats_infos['Average speech'] ?></b></td>
        </tr>
        <tr>
			<td>Response</td>
            <td><b><?php echo $stats_infos['Average response'] ?></b></td>
			<td>Score</td>
            <td><b><?php echo $stats_infos['Average score'] ?></b></td>
        </tr>
        <tr>
			<td>CAPS</td>
            <td><b><?php echo $stats_infos['Average CAPS'] ?></b></td>
			<td>Transfer</td>
            <td><b><?php echo $stats_infos['Average transfer'] ?></b></td>
        </tr>
</table>

<h5>VoiceXML<hr></h5>
<table class="list">
        <tr>
			<td>Prompt</td>
            <td><b><?php echo $stats_infos['Prompts'] ?></b></td>
			<td>Transfer</td>
            <td><b><?php echo $stats_infos['Transfers'] ?></b></td>
        </tr>
        <tr>
			<td>Record</td>
            <td><b><?php echo $stats_infos['Records'] ?></b></td>
			<td>Speech</td>
            <td><b><?php echo $stats_infos['Average score'] ?></b></td>
        </tr>
        <tr>
			<td>Originate</td>
            <td><b><?php echo $stats_infos['Originates'] ?></b></td>
			<td>Originate error</td>
            <td><span style="color: red"><b><?php echo $stats_infos['Originates error'] ?></b></span></td>
        </tr>
</table>


<form action="">
	<br>
	<input type="button" onclick="history.go(0)" value="<?php echo _("Refresh")?>">
</form>
