<?php
// File    : tts.voximalsettings.php
// Version : $Revision: 1.1 $


//init some global variables
$ccache_message="";
$ccache_color="";

//Bind with jaavscript this value
$workingTTS=getWorkingTTS();

if (isset($_POST['form']) && $_POST['form'] == "editsettings") {
	$settings = array (
			"api" => trim($_POST['api']),
			"uri" => trim($_POST['uri']),
			"method" => trim($_POST['method']),
			"format" => trim($_POST['format']),
 			"voice"  => trim($_POST['voice']),
			"speed"  => trim($_POST['speed']),
			"ssml"   => trim($_POST['ssml']),
			"cutprompt" => trim($_POST['cutprompt']),
			"maxage" => trim($_POST['maxage']),
			"id"     => trim($_POST['id']),
			"key"    => trim($_POST['key']),
			"user"   => trim($_POST['user']),
			"password" => trim($_POST['password'])
	);

  	//print_r($settings);
	//modifyVoximalTts($settings);
	savePromptConf($settings);
	needreload();
}

if (isset($_POST['ccache']) ) {
	list($ccache_message,$ccache_color)=clearVoximalCache();
}



// GEt voximal configuration
$settings = getPromptConf(true);

//debug
/*
if(count($_POST))
echo "<pre>POST\n".print_r($_POST,true)."</pre>";
echo "<pre>Settings\n".print_r($settings,true)."</pre>";
 */

//Apis
$tts_apis=array(
	'HTTP'=>'default',
	'MRCP'=>'asterisk',
	'PicoTTS (free)'=>'pico',
	'Voximal Cloud'=>'voximal',
	'Voxygen Cloud'=>'voxygen',
	'Cereproc Cloud'=>'cereproc',
	'iSpeech Text to Speech'=>'ispeech',
	'Microsoft Bing Speech API'=>'microsoft',
	'IBM/Watson Text to Speech'=>'watson',
	'Amazon/Polly Text to Speech'=>'polly',
	'Google Text to Speech'=>'google',
	'Yandex Text to Speech'=>'yandex',
	);

//We check the value of Method
if (trim($settings['method']) == "ASTERISK") {
	$method_asterisk   = "checked";
	$method_post       = "";
	$method_get        = "";
}
else if (trim($settings['method']) == "GET") {
	$method_asterisk   = "";
	$method_post       = "";
	$method_get        = "checked";
}
else {
	$method_asterisk   = "";
	$method_post       = "checked";
	$method_get        = "";
}
//Add format changing only this array
$tts_formats=array('wav'=>'','wav16'=>'','pcm'=>'','alaw'=>'','ulaw'=>'','raw'=>'','sln'=>'','sln16'=>'');
foreach($tts_formats as $ttsf => $val){
	if (trim($settings['format']) == $ttsf){
		$tts_formats[$ttsf]='checked';
	}else{
		$tts_formats[$ttsf]='';
	}
}

//We check the value of SSML
if (trim($settings['ssml']) == 1) {
	$ssml_yes   = "checked";
	$ssml_no    = "";
}
else {
	$ssml_yes   = "";
	$ssml_no    = "checked";
}

//cutprompt
if (trim($settings['cutprompt']) == 1) {
	$cutprompt_yes   = "checked";
	$cutprompt_no    = "";
}
else {
	$cutprompt_yes   = "";
	$cutprompt_no    = "checked";
}


$tabindex = 0;
?>
<form autocomplete="off" name="settings" id="settings" action="config.php?display=voximalsettings&view=tts" method="post">
	<input type="hidden" id="form" name="form" value="editsettings">
	<input type="hidden" id="workingTTS" name="workingTTS" value="<?php echo $workingTTS ?>">
	<table width="700px">
		<tr>
			<td><h5><?php echo _("Synthesis");?><hr></h5></td>
		</tr>
		<tr>
			<td>
			<table width="600px">
			  <td style="width: 25%;"><a href="#" class="info">API*<span><?php echo _("API used, .")?></span></a></td>
			  <td style="width: 75%;">
                	   <select name="api" id="api" style="width: 250px;" >
<?php
		foreach ( $tts_apis as $ApiName=>$ApiCode ){ 
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
foreach($tts_formats as $ttsf => $checked){ ?>
		     <input type="radio" name="format" id="format_<?php echo $ttsf?>" value="<?php echo $ttsf?>" <?php echo $checked?> tabindex="<?php echo $tabindex++?>">
		     <label for="format_<?php echo $ttsf?>"><?php echo $ttsf?></label>
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
					<tr class="trapivoice">
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Voice");?><span><?php echo _("Define voice parameter for specific api.");?></span></a></td>
						<td><input type="text" id="voice" name="voice" style="width: 140px;" value="<?php echo $settings['voice']; ?>" tabindex="<?php echo $tabindex++?>"></td>
					</tr>
					<tr class="trapispeed">
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Speed");?><span><?php echo _("Define speed parameter for specific api.");?></span></a></td>
						<td><input type="text" id="speed" name="speed" style="width: 140px;" value="<?php echo $settings['speed']; ?>" tabindex="<?php echo $tabindex++?>"></td>
					</tr>
		         <tr class="trapissml"><!-- SSML ? -->
				<td style="width: 27%;"><a href="#" class="info"><?php echo _("SSML");?><span><?php echo _("Enable or disbale SSML");?></span></a></td>
					<td>
						<table style="width: 100%; height: 20px;">
						<tbody>
						<tr>
						<td>
						<span class="radioset">
							<input id="ssml_yes" type="radio" name="ssml" value="1" <?php echo $ssml_yes?> tabindex="<?php echo $tabindex++?>">
								<label for="ssml_yes">Yes</label>
							<input id="ssml_no" type="radio" name="ssml" value="0" <?php echo $ssml_no?> tabindex="<?php echo $tabindex++?>">
							<label for="ssml_no">No</label>
						</span>
						</td>
						</tr>
					</tbody>
					</table>
				</td>
			</tr>
			<tr><!-- cache maxage -->
						<td style="width: 27%;"><a href="#" class="info"><?php echo _("Cache ageing");?><span><?php echo _("Define cache maxage parameter in seconds, -1 means no expiry (by default) and 0 means that cache is disabled");?></span></a></td>
						<td><input type="text" id="maxage" name="maxage" style="width: 140px;" value="<?php echo isset($settings['maxage']) ? $settings['maxage'] : '0' ?>" tabindex="<?php echo $tabindex++?>"><span style="color: red;" id="errMaxage"></span></td>
					</tr>

			 <tr>
		         <tr><!-- CutPrompt -->
				<td style="width: 27%;"><a href="#" class="info"><?php echo _("Cut prompt");?><span><?php echo _("For large prompts, cut into small ones, to speed up TTS");?></span></a></td>
					<td>
						<table style="width: 100%; height: 20px;">
						<tbody>
						<tr>
						<td>
						<span class="radioset">
							<input id="cutprompt_yes" type="radio" name="cutprompt" value="1" <?php echo $cutprompt_yes?> tabindex="<?php echo $tabindex++?>">
								<label for="cutprompt_yes">Yes</label>
							<input id="cutprompt_no" type="radio" name="cutprompt" value="0" <?php echo $cutprompt_no?> tabindex="<?php echo $tabindex++?>">
							<label for="cutprompt_no">No</label>
						</span>
						</td>
						</tr>
					</tbody>
					</table>
				</td>
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
						<td><form action="config.php?display=voximalsettings&view=tts" method="post">
							<input type="submit" style="width: 100px; height: 25px;" name="ccache" value="<?php echo _("Cache clear");?>">
		           				<span id="errURL" style="color: <?php echo $ccache_color;?>"><?php echo $ccache_message;?></span>
						   </form>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

<script type="text/javascript">

	//FreePBX embed jquery : cool

	//Load on document ready
	$( document ).ready(function() {
	  	// Handler for .ready() called.
		hiddenotused();
		
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
			$('#uri').val($('#workingTTS').val());
	}

	//Changeing to POST or GET or from 
	$("#method_get").change(onsetpostorget);
	$("#method_post").change(onsetpostorget);

	function cleanextapi(){
		var params= [ "id", "user", "password", "voice", "key", "speed"];
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
			case "ispeech":
			case "watson":
			case "polly":
				cleangenapi();
				break;
			case 'voximal':
			case 'pico':
				cleanextapi();
				cleangenapi();
				break;
			case 'asterisk':
				cleanextapi();
				cleangenapi();
				break;
			default:
				//set default woring HTTP TTS
				$('#uri').val($('#workingTTS').val());
				$('input:radio[name="format"][value="wav16"]').prop('checked',true);
				$('input:radio[name="method"][value="POST"]').prop('checked',true);
				$('input:radio[name="ssml"][value="0"]').prop('checked',true);
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
			case "ispeech":
				$("tr.trapikey").show();
				$("tr.trapiuser").hide();
				$("tr.trapipassword").hide();
				$("tr.trapivoice").show();
        $("tr.trapiext").hide();
				$("tr.trapigen").hide();
				$("tr.trapissml").hide();
				$("tr.trapispeed").hide();
				break;
			case "cereproc":
				$("tr.trapikey").hide();
				$("tr.trapiuser").show();
				$("tr.trapipassword").show();
				$("tr.trapivoice").show();
        $("tr.trapiext").hide();
				$("tr.trapigen").hide();
				$("tr.trapissml").hide();
				$("tr.trapispeed").show();
				break;
			case "watson":
				$("tr.trapikey").hide();
				$("tr.trapiuser").show();
				$("tr.trapipassword").show();
				$("tr.trapivoice").show();
        $("tr.trapiext").hide();
				$("tr.trapigen").hide();
				$("tr.trapissml").hide();
				$("tr.trapispeed").hide();
				break;
			case "pico":
				$("tr.trapikey").hide();
				$("tr.trapiuser").hide();
				$("tr.trapipassword").hide();
				$("tr.trapivoice").hide();
				$("tr.trapiext").hide();
				$("tr.trapigen").hide();
				$("tr.trapissml").hide();
				$("tr.trapispeed").hide();
				break;
			case "voximal":
				$("tr.trapikey").hide();
				$("tr.trapiuser").hide();
				$("tr.trapipassword").hide();
				$("tr.trapivoice").hide();
				$("tr.trapiext").hide();
				$("tr.trapigen").hide();
				$("tr.trapissml").hide();
				$("tr.trapispeed").show();
				break;
			case "polly":
				$("tr.trapikey").show();
				$("tr.trapiuser").hide();
				$("tr.trapipassword").show();
				$("tr.trapivoice").show();
				$("tr.trapiext").hide();
				$("tr.trapigen").hide();
				$("tr.trapissml").show();
				$("tr.trapispeed").hide();
				break;
			case "google":
				$("tr.trapikey").show();
				$("tr.trapiuser").hide();
				$("tr.trapipassword").hide();
				$("tr.trapivoice").show();
        $("tr.trapiext").hide();
				$("tr.trapigen").hide();
				$("tr.trapissml").hide();
				$("tr.trapispeed").hide();
				break;
			case "yandex":
				$("tr.trapikey").show();
				$("tr.trapiuser").hide();
				$("tr.trapipassword").hide();
				$("tr.trapivoice").show();
        $("tr.trapiext").hide();
				$("tr.trapigen").hide();
				$("tr.trapissml").hide();
				$("tr.trapispeed").hide();
				break;
			case "asterisk":
				//mrcp
				$("tr.trapikey").hide();
				$("tr.trapiuser").hide();
				$("tr.trapipassword").hide();
				$("tr.trapivoice").hide();
				$("tr.trapiext").hide();
				$("tr.trapigen").hide();
				$("tr.trapissml").hide();
				$("tr.trapispeed").hide();
        break;
      default:
				//http or mrcp
				$("tr.trapikey").show();
				$("tr.trapiuser").show();
				$("tr.trapipassword").show();
				$("tr.trapivoice").show();
				$("tr.trapiext").show();
				$("tr.trapigen").show();
				$("tr.trapissml").show();
				$("tr.trapispeed").hide();
		}
	}

	function ccache() {
		window.location += "&ccache=true";
	}
	
	function save() {

		document.getElementById("errMaxage").innerHTML = "";

		var form = document.getElementById("settings");
		var maxage = form.elements["maxage"].value;

		var sub_maxage = 0;

		if (isNaN(maxage)) {
			document.getElementById("errMaxage").innerHTML = "  The value must be a number.";
		} else {
			if (!isInt(maxage)) {
				document.getElementById("errMaxage").innerHTML = "  The value must be an integer.";
			} else {
				if (maxage < -1) {
					document.getElementById("errMaxage").innerHTML = "  The value is not in the correct range [-1,0...]";
				} else {
					sub_maxage = 1;
				}
			}
		}

		if (sub_maxage == 1) {
			form.submit();
		}

	}

	function isInt(n) {
		return n % 1 === 0;
	}
</script>
