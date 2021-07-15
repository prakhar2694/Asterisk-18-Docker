<?php
// File    : asr.voximalsettings.php
// Version : $Revision: 1.2 $


//Bind with jaavscript this value
$workingASR=getWorkingASR();

//init some global variables
$jsf_message="";
$jsf_color="";
$myurl='config.php?display=voximalsettings&view=asr';

//var_dump($_POST);

if (isset($_POST['form']) && $_POST['form'] == "editsettings") {
	$settings = array (
			"api" => trim($_POST['api']),
			"uri" => trim($_POST['uri']),
			"method" => trim($_POST['method']),
			"format" => trim($_POST['format']),
			"id"  => trim($_POST['id']),
			"key" => trim($_POST['key']),
			"user"  => trim($_POST['user']),
			"password" => trim($_POST['password']),
			"model" => trim($_POST['model'])
	);

	saveRecognizeConf($settings);
	needreload();
}

//Empty or not set
if ( $_POST['jsonfile'] != "" ) {
	//echo "YES";
	list($jsf_message,$jsf_color)=setJsonFile($_POST['jsonfile']);
}

if (isset($_POST['restartnow']) ) {
	list($jsf_message,$jsf_color)=forceRestartAsterisk();
}


// GEt voximal configuration
$settings = getRecognizeConf(true);

//debug
/*
if(count($_POST))
echo "<pre>POST\n".print_r($_POST,true)."</pre>";
echo "<pre>Settings\n".print_r($settings,true)."</pre>";
 */

//Apis
$asr_apis=array(
	'MRCP'=>'asterisk',
	'Microsoft Bing Speech API'=>'microsoft',
//	'Voximal'=>'voximal',
	'IBM/Watson Speech to Text'=>'watson',
	'Google Speech API'=>'google',
	'Google Speech API (streaming)'=>'google_streaming',
	'Google Dialogflow API (streaming)'=>'google_dialogflow',
	);

//We check the value of Method
if (trim($settings['method']) == "ASTERISK") {
	$method_asterisk   = "checked";
	$method_post       = "";
	$method_get        = "";
}
else if (trim($settings['method']) == "POST") {
	$method_asterisk   = "";
	$method_post       = "checked";
	$method_get        = "";
}
else {
	$method_asterisk   = "";
	$method_post       = "";
	$method_get        = "checked";
}
//Add format changing only this array
$asr_formats=array('wav'=>'','wav16'=>'','pcm'=>'','alaw'=>'','ulaw'=>'','raw'=>'','sln'=>'','sln16'=>'');
foreach($asr_formats as $asrf => $val){
	if (trim($settings['format']) == $asrf){
		$asr_formats[$asrf]='checked';
	}else{
		$asr_formats[$asrf]='';
	}
}


$tabindex = 0;
?>
<form autocomplete="off" name="settings" id="settings" action="<?php echo $myurl?>" method="post">
	<input type="hidden" id="form" name="form" value="editsettings">
	<input type="hidden" id="workingASR" name="workingASR" value="<?php echo $workingASR ?>">
	<table width="700px">
		<tr>
			<td><h5><?php echo _("Recognition");?><hr></h5></td>
		</tr>
		<tr>
			<td>
			<table width="600px">
			  <td style="width: 25%;"><a href="#" class="info">API*<span><?php echo _("API used, .")?></span></a></td>
			  <td style="width: 75%;">
                	   <select name="api" id="api" style="width: 250px;" >
<?php
		foreach ( $asr_apis as $ApiName=>$ApiCode ){
                	echo "                   <option ";
			if($settings['api'] == $ApiCode) echo 'selected';
			echo " value=\"$ApiCode\">$ApiName</option>\n";
		}
?>
                           </select>
		           <span id="errURL" style="color: red"></span>
                          </td>
		       </tr>
    		       <tr class="trapigen">
			       <td style="width: 25%;"><a href="#" class="info">URI*<span><?php echo _("URI used, .")?></span></a></td>
			       <td style="width: 75%;"><input type="text" id="uri" name="uri" style="width: 250px;" value="<?php echo $settings['uri'] ? $settings['uri'] : ""?>"><span id="errURL" style="color: red"></span>
            </td>
		      </tr>
    			<tr class="trapigen">
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Method");?><span><?php echo _("HTTP method, or set Asterisk to send the text to the Asterisk module.");?></span></a></td>
						<td>
							<table style="width: 100%; height: 20px;">
		        				<tbody>
		        					<tr>
		          						<td>
											<span class="radioset">
		            							<input type="radio" name="method" id="method_post" value="POST" <?php echo $method_post?> tabindex="<?php echo $tabindex++?>">
		            								<label for="method_post">POST</label>
		            							<input type="radio" name="method" id="method_get" value="GET" <?php echo $method_get?> tabindex="<?php echo $tabindex++?>">
		            								<label for="method_get">GET</label>
		            							<input type="radio" name="method" id="method_asterisk" value="ASTERISK" <?php echo $method_asterisk?> tabindex="<?php echo $tabindex++?>">
		            								<label for="method_asterisk">ASTERISK</label>
											</span>
		          						</td>
		        					</tr>
		      					</tbody>
	      					</table>
						</td>
					</tr>
    			<tr class="trapigen">
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Format");?><span><?php echo _("Format expected for audio files.");?></span></a></td>
						<td>
							<table style="width: 100%; height: 20px;">
		        				<tbody>
		        					<tr>
		          						<td>

						<span class="radioset">
<?php
foreach($asr_formats as $asrf => $checked){ ?>
		     <input type="radio" name="format" id="format_<?php echo $asrf?>" value="<?php echo $asrf?>" <?php echo $checked?> tabindex="<?php echo $tabindex++?>">
		     <label for="format_<?php echo $asrf?>"><?php echo $asrf?></label>
<?php }?>

						</span>
		          						</td>
		        					</tr>
		      					</tbody>
	      					</table>
						</td>
					</tr>
 					<tr class="trapiext">
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Id");?><span><?php echo _("Define id parameter for specific api.");?></span></a></td>
						<td><input type="text" id="id" name="id" style="width: 140px;" value="<?php echo $settings['id']; ?>" tabindex="<?php echo $tabindex++?>"></td>
					</tr>
					<tr>
					<tr class="trapikey">
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Key");?><span><?php echo _("Define key parameter for specific api.");?></span></a></td>
						<td><input type="text" id="key" name="key" style="width: 140px;" value="<?php echo $settings['key']; ?>" tabindex="<?php echo $tabindex++?>"></td>
					</tr>
					<tr class="trapiuser">
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("User");?><span><?php echo _("Define user parameter for specific api");?></span></a></td>
						<td><input type="text" id="user" name="user" style="width: 140px;" value="<?php echo $settings['user']; ?>" tabindex="<?php echo $tabindex++?>"></td>
					</tr>
					<tr class="trapipassword">
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Password");?><span><?php echo _("Define password parameter for specific api");?></span></a></td>
						<td><input type="text" id="password" name="password" style="width: 140px;" value="<?php echo $settings['password']; ?>" tabindex="<?php echo $tabindex++?>"></td>
					</tr>
					<tr class="trapimodel">
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Model");?><span><?php echo _("Define model parameter for specific api.");?></span></a></td>
						<td><input type="text" id="model" name="model" style="width: 140px;" value="<?php echo $settings['model']; ?>" tabindex="<?php echo $tabindex++?>"></td>
					</tr>
                                   	<tr class="trapijsonfile">
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Json Auth File");?><span><?php echo _("Fill in the content of the json service account key file.");?></span></a></td>
						<td><textarea id="jsonfile" name="jsonfile" rows="10" cols="33" wrap="off" tabindex="<?php echo $tabindex++?>"><?php echo getJsonFile(); ?></textarea></td>
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
						<td><form action="<?php echo $myurl?>" method="post">
							<input type="submit" style="width: 140px; height: 25px;" name="restartnow" value="<?php echo _("Restart Asterisk");?>">
		           				<span id="errURL" style="color: <?php echo $jsf_color;?>"><?php echo $jsf_message;?></span>
						   </form>
						</td>
						
					</tr>
				</table>
			</td>
		</tr>
	</table>
<br><?php 
$ut=getAsteriskUptime();
$reloadin='';
if( $ut == ''){
	//add reload auto after 3s
	$reloadin="setTimeout( function (){
window.location.href='$myurl';},
3000);";
}else{ 
	echo _('Asterisk uptime: ').$ut;
}
?> 
<script type="text/javascript">

	//FreePBX embed jquery : cool

	//Load on document ready
	$( document ).ready(function() {
	  	// Handler for .ready() called.
		hiddenotused();
		<?php echo "$reloadin"?>
		
	});

	
	//Changeing to ASTERISK
	$("#method_asterisk").change(function() {
	    if(this.checked) {
		//Enabling ASTERISK method, check uri is rtsp
		var uri= $('#uri').val();
		if( uri.substring(0,3) != 'rtsp') 
			$('#uri').val('rtsp://localhost:554');
	    }
	});

	function onsetpostorget(){
		//Enabling GET or POST method, check uri is http
		var uri= $('#uri').val();
		if( uri.substr(0,3) != 'http')
			$('#uri').val($('#workingASR').val());
	}

	//Changeing to POST or GET or from 
	$("#method_get").change(onsetpostorget);
	$("#method_post").change(onsetpostorget);

	function cleanextapi(){
		var params= [ "id", "user", "password", "model", "key" ];
		jQuery.each( params, function( i, param ) {
		$('#'+param).val('');
		});
	}
	function cleangenapi(){
		var formats= ['wav','wav16','pcm','alaw','ulaw','raw','sln16','sln'];
		jQuery.each( formats, function( i, format ) {
			$('input:radio[name="format"][value="'+format+'"]').prop('checked',false);
		});
		$('input:radio[name="method"][value="POST"]').prop('checked',false);
		$('input:radio[name="method"][value="GET"]').prop('checked',false);
		$('input:radio[name="method"][value="ASTERISK"]').prop('checked',false);
		$('#uri').val('');
	}

	//Handler on api combo change
	$("#api").change( function(){
		//alert( "Handler for change() called config" );
		//Cleaning unsed params, for a simpler conf file
		var api = $('#api').val();
		switch(api){
			case "voxygen":
			case "microsoft":
			case "cereproc":
			case "watson":
			case "google":
			case "google_streaming":
			case "google_dialogflow":
				cleangenapi();
				break;
			case 'voximal':
				cleanextapi();
				cleangenapi();
				break;
			default:
				//set default woring HTTP ASR
				$('#uri').val($('#workingASR').val());
				$('input:radio[name="format"][value="wav16"]').prop('checked',true);
				$('input:radio[name="method"][value="POST"]').prop('checked',true);
				cleanextapi();
				break;
		}
		hiddenotused();
	});
	
	//Just hide or show accordinf to api value
	function hiddenotused(){
		switch($('#api').val()){
			case "voxygen":
			case "microsoft":
			case "cereproc":
			case "google":
				$("tr.trapikey").show();
				$("tr.trapiuser").hide();
				$("tr.trapipassword").hide();
				$("tr.trapimodel").hide();
				$("tr.trapiext").hide();
				$("tr.trapigen").hide();
				$("tr.trapijsonfile").hide();
				break;
			case "google_streaming":
			case "google_dialogflow":
				$("tr.trapikey").hide();
				$("tr.trapiuser").hide();
				$("tr.trapipassword").hide();
				$("tr.trapimodel").hide();
				$("tr.trapiext").hide();
				$("tr.trapigen").hide();
				$("tr.trapijsonfile").show();
				break;
			case "watson":
				$("tr.trapikey").hide();
				$("tr.trapiuser").show();
				$("tr.trapipassword").show();
				$("tr.trapimodel").show();
				$("tr.trapiext").hide();
				$("tr.trapigen").hide();
				$("tr.trapijsonfile").hide();
				break;
			case "voximal":
      	$("tr.trapikey").hide();
				$("tr.trapiuser").hide();
				$("tr.trapipassword").hide();
				$("tr.trapimodel").hide();
				$("tr.trapiext").hide();
				$("tr.trapigen").hide();
				$("tr.trapijsonfile").hide();
				break;
			case "asterisk":
      	$("tr.trapikey").hide();
				$("tr.trapiuser").hide();
				$("tr.trapipassword").hide();
				$("tr.trapimodel").hide();
				$("tr.trapiext").hide();
				$("tr.trapigen").hide();
				$("tr.trapijsonfile").hide();
				break;
			default:
				//http or mrcp
				$("tr.trapikey").show();
				$("tr.trapiuser").show();
				$("tr.trapipassword").show();
				$("tr.trapimodel").show();
				$("tr.trapijsonfile").show();
				$("tr.trapiext").show();
				$("tr.trapigen").show();
		}
	}

	function save() {

		var form = document.getElementById("settings");

		if (true) {
			form.submit();
		}

	}

	function isInt(n) {
		return n % 1 === 0;
	}
</script>
