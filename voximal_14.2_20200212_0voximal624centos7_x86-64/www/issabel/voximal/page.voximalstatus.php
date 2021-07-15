<?php
// File    : page.voximalstatistics.php
// Version : $Revision: 1.1 $


//Check if user is "logged in"
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$stats_infos['Sessions pending'] = "0";
$stats_infos['Sessions peak']    = "0";
$stats_infos['Average sessions'] = "0";

$no_accounts_configured = false;


global $astman;
if ($astman) {
	$stats = $astman->send_request('Command',array('Command'=>"voximal show statistics"));
	$stats = explode("\n",$stats['data']);
	$dates = $astman->send_request('Command',array('Command'=>"voximal show dates"));
	$dates = explode("\n",$dates['data']);
//	$version = $astman->send_request('Command',array('Command'=>"voximal show version"));
//	$version = explode("\n",$version['data']);
	$license = $astman->send_request('Command',array('Command'=>"voximal show license"));
	$license = explode("\n",$license['data']);
	$sipstatus = $astman->send_request('Command',array('Command'=>"sip show registry"));
	$sipstatus = explode("\n",$sipstatus['data']);
	$accounts  = $astman->send_request('Command',array('Command'=>"voximal show accounts"));
	$accounts  = explode("\n",$accounts['data']);

    // Get sip registry infos
    $pos = false;
	foreach ($sipstatus as $line) {
		if (trim($line) != '') {
			if ($pos===false) {
				// find the position of "State" in the first line
				$pos = strpos($line,"State");
			} else {
                if (strpos($line,"registrations")) {
                    list($nbsipreg,$prot,$reg) = explode(" ",$line);
                }
                else {
                    $elts = preg_split("/ +/", $line);
                    //print_r($elts);
                    if (count($elts) > 2) {
                        $infos["host"]     = trim($elts[0]);
                        $infos["username"] = trim($elts[2]);
                        $infos["state"]    = trim($elts[4]);
                        $sip_infos[] = $infos;
                    }
                }
			}
		}
	}

    // Get max sessions and config
    foreach ($license as $line) {
        if (strpos($line,":") !== false) {
            list($key, $val) = explode(":", $line);
            $license_infos[rtrim(ltrim($key))] = trim(ltrim($val));
        }
    }
    $listModEnable = "VoiceXML";
    if ($license_infos["TextToSpeech"] == "Yes")
        $listModEnable .= " - TTS";
    if ($license_infos["Speech"] == "Yes")
        $listModEnable .= " - ASR";

    // Get stats
    foreach ($stats as $line) {
        //issabelpbx_log(FPBX_LOG_CRITICAL,"==== DBGJYG: vxmlstats $line");
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
            $value = explode(":", $line);
            if (count($value) == 1)
            $value[1]="";
            list($key, $val) = $value;
            $dates_infos[rtrim(ltrim($key))] = trim(ltrim($val));
        }
    }
    // Check accounts
    //  ivr*CLI> voximal show accounts
    //  No accounts configured !
    if (count($accounts) > 1) {
        if (strpos($accounts[1], "No accounts") !== false) {
            $no_accounts_configured = true;
        }
    }
}

// Get voximald state
$out = shell_exec("pidof voximald | wc -w");
if ($out == 0) {
	$voximald_state = 'Not running';
    //$iconVoximal = FreePBX::Dashboard()->genStatusIcon("critical", _("Voximal not running"));
}
else {
	$voximald_state = 'Running';
    //$iconVoximal = FreePBX::Dashboard()->genStatusIcon("ok", _("Voximal is running"));
}

// Get license info
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


$color_high   = "#FE3210";
$color_medium = "#FE801E";
$color_low    = "#FFE856";

$max_sessions = $license_infos["Max sessions"];
$peak = $stats_infos["Sessions peak"];

$per_peak   = $stats_infos['Sessions peak'] * 100 / $max_sessions;
$color_peak = $color_medium;
if ($per_peak >= 80)
    $color_peak = $color_high;
else if ($per_peak <= 20)
    $color_peak = $color_low;

if ($license_infos["State"] == "ok") {
    //$iconLicense = FreePBX::Dashboard()->genStatusIcon("ok", _("License is ok"));
}
else {
    //$iconLicense = FreePBX::Dashboard()->genStatusIcon("critical", _("License state:").$license_infos["State"]);
}

/*
$file = 'http://www.domain.com/somefile.jpg';
$file_headers = @get_headers($file);
if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
    $exists = false;
}
else {
    $exists = true;
}
*/

// Disk usage
$callRecording = trim(shell_exec('du -hs /var/spool/asterisk/monitor | awk \'{ print $1 }\''));
$interpreterCache = trim(shell_exec('du -csh /tmp/cacheContent /tmp/logContent | awk \'/total/ { print $1}\''));
$ret_cmdline = preg_split("/ +/", trim(shell_exec('df -H --total | grep total ')));

$diskSize = $ret_cmdline[1];
$diskUsed = $ret_cmdline[2];
$diskAvailable  = $ret_cmdline[3];
$diskUsePercent = $ret_cmdline[4];
$valPercent = substr($diskUsePercent, 0, -1);

// Top
$cpu_idle = 0;
$mem_use = 0;
$mem_tt = 0;
exec("TERM=xterm /usr/bin/top n 1 b i", $output, $ret);
foreach ($output as $line){
    if (preg_match("/^Cpu/", $line)) {
        $elts = preg_split("/ +/", trim($line));
        $cpu_user = substr($elts[1], 0, strpos($elts[1], "%"));
        $cpu_sys  = substr($elts[2], 0, strpos($elts[2], "%"));
        $cpu_idle = substr($elts[4], 0, strpos($elts[4], "%"));
    }
    if (preg_match("/^Mem/", $line)) {
        $elts = preg_split("/ +/", trim($line));
        $mem_tt  = $elts[1];
        $mem_use = $elts[5];
    }
}
//echo  "CPU: user:$cpu_user sys:$cpu_sys idle:$cpu_idle <br>";
//echo  "MEM: $mem_use/$mem_tt<br>";

/*exec('TERM=xterm /usr/bin/top n 1 b i', $top, $error );
echo nl2br(implode("\n",$top));
if ($error){
    exec('TERM=xterm /usr/bin/top n 1 b 2>&1', $error );
    echo "Error: ";
    exit($error[0]);
} */


?>

<!--
<style TYPE = "text/css">
.tableresume {
    font-family: verdana;
    font-size: 10pt;
    line-height: 0.5;
    text-align: left;
}
.tableresume td:nth-child(1){
    width: 200px;
}

.showtable, .showtable TD, .showtable TH
{
font-family: verdana;
font-size: 10pt;
line-height: 0.5;
text-align: left;
}
</style>
-->
<h2><?php echo _("Voximal Status")?></h2>
<br>
    <table class="tableresume">
        <tr>
			<td ><?php echo _("Start")?></td>
            <td><?php echo $dates_infos["Start module"]; ?></td>
        </tr>
        <tr>
			<td ><?php echo _("Reload configuration")?></td>
            <td><?php echo $dates_infos["Reload configuration"]; ?></td>
        </tr>
        <tr>
			<td ><?php echo _("Process")?></td>
            <?php if ($voximald_state == "Running") {?>
            <td>
            <div class="status-element" data-toggle="tooltip" title="<?php echo $iconVoximal['tooltip']?>">
        		 <div class="status-icon"><span class="glyphicon <?php echo $iconVoximal['glyph-class']?>"></span></div>
        		<?php echo (isset($iconVoximal['title'])) ? $iconVoximal['title'] : "?"; ?>
			</div></td>
            <?php } else { ?>
            <td><span style="color: red"><b><?php echo $voximald_state; ?></b></span></td>
            <?php } ?>
        </tr>
		<tr id="tr_licMngStatus" style="display: <?php echo (isset($dispRemoteLic)) ? $dispRemoteLic : "?" ?>">
			<td style="width: 30%;"><?php echo _("License");?></td>
            <?php if ($licExpired) { ?>
			<td style="width: 70%;"><b><span style="font-family: Verdana; color: red;"><?php echo _("Expired");?></span></b></td>
            <?php } else if ($licWithExpiration) { ?>
			<td style="width: 70%;"><span style="font-family: Verdana; color: orange;"><?php echo _("Expiration");?> : <?php echo $expdate;?></span></td>
            <?php } else { ?>
			<!-- <td style="width: 70%;"><?php echo $license_infos["State"];?></td> -->
            <td>
            <div class="status-element" data-toggle="tooltip" title="<?php echo $iconLicense['tooltip']?>">
        		 <div class="status-icon"><span class="glyphicon <?php echo (isset($iconLicense['glyph-class'])) ? $iconLicense['glyph-class'] : "?" ?>"></span></div>
        		<?php echo isset($iconLicense['title']) ? $iconLicense['title'] : "?"; ?>
			</div>
            </td>
            <?php }?>
		</tr>
        <tr>
			<td ><?php echo _("Options")?></td>
            <td><?php echo $listModEnable; ?></td>
        </tr>
        <tr>
			<td ><?php echo _("Max sessions")?></td>
            <td><?php echo $max_sessions; ?></td>
        </tr>
        <?php if ($peak >= $max_sessions) {?>
        <tr>
			<td >&nbsp;</td>
            <td><span style="color: orange"><b><?php echo _("Warning: The session peak attemp max sessions")?></span></td>
        </tr>
        <?php } ?>
        <?php if ($stats_infos['Average response'] >= 3) {?>
        <tr>
			<td ><?php echo _("Response time")?></td>
            <td><span style="color: orange"><b><?php echo _("response time seams to be high") . " : " .$stats_infos['Average response']?></span></td>
        </tr>
        <?php } ?>
        <?php if ($no_accounts_configured) {?>
        <tr>
			<td ><?php echo _("Accounts")?></td>
            <td><span style="color: red"><b><?php echo _("Warning: no accounts configured")?></span></td>
        </tr>
        <?php } ?>
    </table>
<br>
<h5><?php echo _("Connectivity")?><hr></h5>
<table class="showtable">
    <?php
    if ($nbsipreg) {
        foreach ($sip_infos as $sip) {?>
        <tr>
			<td><?php echo $sip["username"];?></td>
			<td><?php echo $sip["state"];?></td>
			<!-- <td>(<?php echo $sip["host"];?>)</td> -->
        </tr>
    <?php }
    }
    else { ?>
        <tr><td><?php echo _("No connection with operator configured");?></td></tr>
    <?php } ?>
</table>

<h5><?php echo _("Connections")?><hr></h5>
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

<h5><?php echo _("Server use")?><hr></h5>
<table class="showtable">
  <tr>
	<td><?php echo _("CPU idle");?></td>
    <?php if ($cpu_idle < 30) {;?>
    <td><span style="color: red"><b><?php echo $cpu_idle;?>%</b></span></td>
    <?php } else {;?>
    <td><?php echo $cpu_idle;?>%</td>
    <?php };?>
  </tr>
  <tr>
	<td><?php echo _("Memory");?></td>
    <td><?php echo "$mem_use/$mem_tt"?></td>
  </tr>
</table>

<h5><?php echo _("Disk usage")?><hr></h5>
<table class="showtable">
  <tr>
	<td><?php echo _("Size");?></td>
    <td><?php echo _("Available");?></td>
    <td><?php echo _("Used");?></td>
	<td>&nbsp;</td>
    <td><?php echo _("Call recording");?></td>
    <td><?php echo _("Cache interpreter");?></td>
  </tr>
  <tr>
	<td><?php echo $diskSize;?></td>
    <td><?php echo $diskAvailable;?></td>
    <?php if ($valPercent > 85) {;?>
    <td><span style="color: red"><b><?php echo $diskUsed . "(".$diskUsePercent.")";?></b></span></td>
    <?php } else {;?>
    <td><?php echo $diskUsed . "(".$diskUsePercent.")";?></td>
    <?php };?>
	<td>&nbsp;</td>
	<td><?php echo $callRecording;?></td>
    <td><?php echo $interpreterCache;?></td>
  </tr>
</table>

<!--
<form action="">
	<br>
	<input type="button" onclick="history.go(0)" value="<?php echo _("Refresh")?>">
</form>
-->
