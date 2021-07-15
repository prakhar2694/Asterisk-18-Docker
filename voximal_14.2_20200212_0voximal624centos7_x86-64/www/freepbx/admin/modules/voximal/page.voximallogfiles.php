<?php 
// File    : page.voximallogfiles.php
// Version : $Revision: 1.9 $


//Check if user is "logged in"
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

/*$loglist = array(
			'apache' => trim(shell_exec("grep 'client.log.apache' /etc/voximald.conf | awk '{ print $3 }'")),
			'voximal' => trim(shell_exec("grep 'client.log.filename' /etc/voximald.conf | awk '{ print $3 }'")),
			'voicexml' => trim(shell_exec("grep 'client.log.voicexml' /etc/voximald.conf | awk '{ print $3 }'"))
			); */

// Check logfile activated
/*   client.log.filename          VXIString   /tmp/log.txt
 *   #client.log.apache           VXIString   /tmp/apache.log
 *   #client.log.voicexml         VXIString   /tmp/voicexml.log
 */

$loglist["voximal"] = "/var/log/voximal/vxml.log";
$loglist["voximal-debug"] = "/var/log/voximal/debug.log";



$selected = isset($_POST['file']) ? trim($_POST['file']) : $loglist['voximal'];


?>

<h2><?php echo _("Voximal Log Files")?></h2>

<form autocomplete="off" name="logfiles" id="logfiles" method="post" action="config.php?display=voximallogfiles"  onload="setLogName();">
	<select name="file">
		<?php 
			foreach ($loglist as $logtype => $logfile) {
				if ($logfile == $selected) {
					echo "<option value='$logfile' selected='selected'>$logtype</option>";
				} else {
					echo "<option value='$logfile'>$logtype</option>";
				}
			}
		?>
	</select>
	<input type="text" name="lines" id="lines" value="<?php echo isset($_POST['lines']) ? trim($_POST['lines']) : "1000"?>">
	<input type="submit" value="<?php echo _("Show")?>">
	
</form>
<br>

<div id="log" style="background-color: #0f192a; color: white; border-radius: 10px; font-family: 'Courier New', Courier, monospace; font-size: 0.85em; overflow: scroll; padding: 10px;">

	<?php 
		echo highlightVOXIMALLog(isset($_POST['file']) ? trim($_POST['file']) : $loglist['voximal'],isset($_POST['lines']) ? trim($_POST['lines']) : "1000");
	?>

</div>

<script type="text/javascript">

	$(document).ready(function() {
		$('#log').css('max-height',($(window).height() - 0.3*$(window).height()));

		$(window).resize(function() {
			$('#log').css('max-height',($(window).height() - 0.3*$(window).height()));
		})
	});
	
</script>
