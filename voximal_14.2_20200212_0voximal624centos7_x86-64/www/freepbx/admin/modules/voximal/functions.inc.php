<?php
// File    : function.inc.php
// Version : $Revision: 1.58 $


//This wil acctivate execption catching on notice and warrnings : debug only
//error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);


// Freepbx callback
function voximal_get_config($engine) {
//freepbx_log(FPBX_LOG_CRITICAL,"==== DBGJYG: voximal_get_config called!");

	//Call to the function to write the file /etc/asterisk/voximal.conf
	writeVoximalConf();

	//We write the dialplan
	global $ext;

	switch($engine) {
		case "asterisk":
			$voximallist = getVoximalList("*");
			foreach ($voximallist as $voximal) {
				$ename = "app-voximal-".$voximal['id'];
				$ext->addSectionComment($ename, $voximal['name']);
				$ext->add($ename,'s','',new ext_voximal($voximal['name']));
				//$ext->add($ename,'s','',new ext_goto($voximal['goto'])); UNUSED
			}
			break;

	}

  updateIAXConf(4569);

	return;

}

function highlightVOXIMALLog($file,$lines) {

	$html = "<span>";
	if(strpos($file,"apache") === false && strpos($file,"voicexml") === false ) {
		$lines = loadVoximalLog($file,$lines);
		/*$html = $html.'<span style="font-style:italic;"><b>Warning</b>. The lines like:<br>
						|0|2000|Read|exiting: 0, 400<br>
						|0|3000|SBinetChannel::Read|exiting, returned <br>
						|0|3000|SBinetChannel::Read|entering: 0x0x6ce9e0, 0x0x7ffff3eff710, 400, 140737285977784, 0x0x7fffe43d2420<br>
						|0|2000|Read|entering: 0x0x6ce968, 0x0x7ffff3eff710, 400, 0x0x7ffff3eff6b8, 0x0x7fffe43a4cc0<br>
						|0|2003|Read|swi:swi:SBinet:http://example.url/yes.bnf, /tmp/cacheContent/swi_SBinet/0/8.sbc: 400 bytes, 400 requested, rc = 0<br><br>

						have been removed for a better reading of the log.<br><br></span><b>Here the log:</b><br><br>';*/
        $html = $html."<b>Here the logs:</b><br><br>\n";

		foreach ($lines as $line) {
			if (empty($line)) continue;
			$color_section = 1;
			if (strpos($line,"|DEV|") !== false) {
				$line = "<span style='color: #00FF00; font-weight: bold;'>".$line."</span>\n";
				$color_section = 0;
			} elseif (strpos($line,"|CRITICAL|") !== false) {
				$line = "<span style='color: #BC1212; font-weight: bold;'>".$line."</span>\n";
				$color_section = 0;
			} elseif (strpos($line,"error.") !== false) {
				$line = "<span style='color: red; font-weight: bold;'>".$line."</span>\n";
				$color_section = 0;
			} elseif (strpos($line,"|MSG") !== false) {
				$line = "<span style='color: #00FFFF; font-weight: bold;'>".$line."</span>\n";
				$color_section = 0;
			} elseif (strpos($line,"|Queuing TTS") !== false) {
				$line = "<span style='color: yellow; font-weight: bold;'>".$line."</span>\n";
				$color_section = 0;
			} elseif (strpos($line,"|WARNING|") !== false) {
				$line = "<span style='color: orange; font-weight: bold;'>".$line."</span>\n";
				$color_section = 0;
			} elseif (strpos($line,"|CRITICAL|") !== false) {
				$line = "<span style='color: red; font-weight: bold;'>".$line."</span>\n";
				$color_section = 0;
			} elseif (strpos($line,"|Waiting CALL") !== false) {
				$line = "<span style='font-weight: bold;'>".$line."</span>\n";
				$color_section = 0;
			}
			if ($color_section) {
				$sections = explode('|',$line);
				if ($sections[3] >= 3000 && $sections[3] < 4000) $colored = "<span style='color: #FF0000;'>".$sections[3]."</span>\n";
				elseif ($sections[3] >= 4000 && $sections[3] < 5000) $colored = "<span style='color: #19B319;'>".$sections[3]."</span>\n";
				elseif ($sections[3] >= 5000 && $sections[3] < 8000) $colored = "<span style='color: #2288EE;'>".$sections[3]."</span>\n";
				elseif ($sections[3] >= 8000 && $sections[3] < 9000) $colored = "<span style='color: #FF00FF;'>".$sections[3]."</span>\n";
				elseif ($sections[3] >= 10000 && $sections[3] < 11000) $colored = "<span style='color: #BA8484;'>".$sections[3]."</span>\n";
        else $colored="";
				$line = substr($line,0,strpos($line,$sections[3])).$colored.substr($line,strpos($line,$sections[3]) + strlen($sections[3]));
			}
			$html = $html.$line."<br>\n";
		}
	} else {
		$lines = loadVoximalLog($file,$lines);
		foreach ($lines as $line) {
			if (empty($line)) continue;
			$html = $html.$line."<br>\n";
		}
	}

	$html = $html."</span>\n";
	return $html;
}

function loadVoximalLog($file,$lines) {

	$filecontent;
	if(strpos($file,"log.txt") !== false) {
		//We ignore the lines that contain the following expresions
		$ignored_lines = ".c:
						|\|Read\|exiting:
						|\|SBinetChannel::Read\|exiting, returned
						|\|SBinetChannel::Read\|entering:
						|\|Read\|entering:
						|\|Read\|swi:swi:SBinet:";
		$filecontent = shell_exec("egrep -v '$ignored_lines' $file | tail -n $lines");
	} else {
		$filecontent = shell_exec("tail -n $lines $file");
	}
	$filecontent = explode("\n",$filecontent);
	return $filecontent;

}


// Freepbx callback
function voximal_destinations() {

	//get the list of Accounts and Applications
	$applications = getVoximalList("*");

	// return an associative array with destination and description
	if (isset($applications)) {
		foreach ($applications as $app) {
			$extens[] = array('destination' => 'app-voximal-'.$app['id'].',s,1', 'description' => $app['name'], 'category' => 'Voximal Application', 'id' => 'voximal');
		}
	}

	if (isset($extens)) {
		return $extens;
	} else {
		return null;
	}

}

function addVoximal($voximal) {
	global $db;
    $list_fields = array("name",    "url",      "maxsessions", "dialformat",
                         "mark", "maxtime", "vxmlparam","startdelay", "speech", "speechprovider");
    $lastkey = "speechprovider";

	/*$voximallist = getVoximalList("*");
	$id = count($voximallist) + 1;*/
	//$sql = 'INSERT INTO voximal VALUES ("'.$id.'","'.$voximal['name'].'","'.$voximal['url'].'","'.$voximal['maxsessions'].'","'.$voximal['dialformat'].'","'.$voximal['mark'].'","'.$voximal['speech'].'","'.$voximal['speechprovider'].'","'.$voximal['goto'].'")';
    $sql = 'INSERT INTO voximal (';
    foreach($list_fields as $key){
        if ($key == $lastkey)
            $sql .= $key;
        else
            $sql .= $key. ',';
    }
    $sql .= ') VALUES (';
    foreach($list_fields as $key){
        if ($key == $lastkey)
            $sql .= '"' .$voximal[$key]. '"';
        else
            $sql .= '"' .$voximal[$key]. '",';
    }
    $sql .= ');';
//freepbx_log(FPBX_LOG_CRITICAL,"==== DBGJYG: $sql");
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_freepbx($result->getMessage().$sql);
	}

	return;

}

function updateVoximal($updateName,$voximal) {

	global $db;
    $list_fields = array("name",    "url",      "maxsessions", "dialformat",
                         "mark", "maxtime", "vxmlparam", "startdelay", "speech", "speechprovider",
                        );
    $lastkey = "speechprovider";

	//$sql = 'UPDATE voximal SET name="'.$voximal['name'].'",url="'.$voximal['url'].'",maxsessions="'.$voximal['maxsessions'].'",dialformat="'.$voximal['dialformat'].'",mark="'.$voximal['mark'].'",speech="'.$voximal['speech'].'",speechprovider="'.$voximal['speechprovider'].'",goto="'.$voximal['goto'].'","'.$voximal['startdelay'].'","'.$voximal['vxmlparam'].'","'.$voximal['maxtime'].'" WHERE name="'.$updateName.'"';
    $sql = 'UPDATE voximal SET ';
    foreach($list_fields as $key){
        if ($key == $lastkey)
            $sql .= $key.'="' .$voximal[$key]. '"';
        else
            $sql .= $key.'="' .$voximal[$key]. '",';
    }
    $sql .= ' WHERE name="'.$updateName.'"';
//freepbx_log(FPBX_LOG_CRITICAL,"==== DBGJYG: $sql");
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_freepbx($result->getMessage().$sql);
	}
	return;

}

function deleteVoximal($name) {

	global $db;

	$sql = 'DELETE FROM voximal WHERE name="'.$name.'"';
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_freepbx($result->getMessage().$sql);
	}
	return;

}

function getVoximalList($param) {

	global $db;

	$sql = "SELECT $param FROM voximal ORDER BY name";
	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

	return $results;

}

function getVoximal($name) {

	global $db;

	$sql = "SELECT * FROM voximal where name='$name'";
	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

	return $results;

}

function getVoximalLicense() {

	global $db;

	$sql = "SELECT * FROM voximallicense";
	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

	$results = $results[0];
	return $results;

}

function modifyVoximalLicense($key) {

	global $db;

	$prevlicense = getVoximalLicense();
	if (empty($prevlicense)) {
		$sql = "INSERT INTO voximallicense (licensekey) VALUES ('".$key."')";
	} else {
		$sql = "UPDATE voximallicense SET licensekey='".$key."' WHERE id='".$prevlicense["id"]."'";
	}
    freepbx_log(FPBX_LOG_CRITICAL,"==== DBGJYG: modifyVoximalLicense: exec $sql");
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_freepbx($result->getMessage().$sql);
	}

	return;

}

//class for the generation of the function Voximal() in the dialplan
class ext_voximal {
	var $dest;
	function __construct($dest) {
		$this->dest = $dest;
	}

	function output() {
		return "Voximal($this->dest)";
	}
}

function clearVoximalCache() {
	global $astman;
  	if ($astman) {
		$out = $astman->send_request('Command',array('Command'=>"voximal cache clear"));
  		$out = explode("\n",$out['data']);
		// Normal Output
		// [0] => Privilege: Command
		// [1] => Deleted files: 0
		// [2] =>
		//try to parse delete files or error
		$res=preg_grep('/Deleted files: (\d+)/',$out);
		if(count($res)){
			preg_match('/Deleted files: (\d+)/', implode('',$res), $matches);
			if(array_key_exists(1,$matches)){
				return array(' cleared '.$matches[1].' file(s)','');
			}else{
				return array('Parse Error','red');
			}
		}else{
			return array('Commad Error','red');
		}
	}
	return array('Cnx Error','red');
}
//Force asterisk restart for Recognize TAB
function forceRestartAsterisk() {
	//We will restart asterisk if uptime is higher than "minutes"
	global $astman;
	$ret=false;
	//We don't use astman, it freeze current php process 
	system("nohup asterisk -rx 'core restart now'",$ret);
	if($ret=='0'){
		return array('Forced restart: wait 3s...','');
	}else{
		return array('Error','red');
	}
}
function getAsteriskUptime() {
	global $astman;
  	if ($astman) {
		$out = $astman->send_request('Command',array('Command'=>"core show uptime"));
  		$out = explode("\n",$out['data']);
		//var_dump($out);
		//[1]=> string(24) "System uptime: 8 seconds"
		//[2]=> string(22) "Last reload: 8 seconds" 
		// "System uptime: 1 minute, 15 seconds"
		// "System uptime: 5 weeks, 1 day, 10 hours, 23 minutes, 45 seconds"
		return str_replace('System uptime: ','',$out[1]);
	}
	return array('Cnx Error','red');
}



//This function will create the content of the file /etc/asterisk/voximal.conf
function writeVoximalConf() {

	global $astman;
//freepbx_log(FPBX_LOG_CRITICAL,"==== DBGJYG: writeVoximalConf called!");

	$confFile = "/etc/asterisk/voximal.conf";
	$content = "
;--------------------------------------------------------------------------------;
; Do NOT edit this file as it is auto-generated by FreePBX. All modifications to ;
; this file must be done via the web gui. There are alternative files to make    ;
; custom modifications.                                                          ;
;--------------------------------------------------------------------------------;

;*********************************************************************************
; AUTO-GENERATED AND CUSTOM USER VOXIMAL CONFIGURATION INCLUDED HERE             *
;*********************************************************************************

[general]";

	//$settings = getVoximalConfiguration('*');
  $settings = getGeneralConf(true);
//print_r($settings);
	$settingsArrayKeys = array_keys($settings);
	var_dump($settingsArrayKeys);
	$content = $content."\n";
  /*
	$content = $content."autoanswer=yes\n";
	$content = $content."videosilence=\n";
	$content = $content."audiosilence=\n";
	$content = $content."speechprovider=unimrcp\n";
	$content = $content."speechscore=30\n";
  */
	$content = $content."speechbeepstart=speech_start\n";
	$content = $content."speechbeepstop=speech_stop\n";

	foreach ($settingsArrayKeys as $key) {
		$value = trim($settings[$key]);
		//if (!empty($value))
    {
			if ($key == "id" ) {
				continue;
			} elseif ($key == "wavdefaulcodec") {
				$content = $content."wavcodec=".strtolower($settings[$key])."\n";
			} elseif ($key == "dialformat") {
				$content = $content."dialformat=".strtolower($settings[$key])."\n";
			} elseif ($key == "monitor") {
				if (strcasecmp($settings['monitor'], "yes") == 0)
        $monitor = "yes";
				else
        $monitor = "no";
				$content = $content."monitordirectory=". "@freepbx" ."\n";
				$content = $content."monitor=". $monitor ."\n";
			} else {
				$content = $content.$key."=".strtolower($settings[$key])."\n";
			}
		}
	}

	$content = $content."\n#include voximal_general_custom.conf\n\n";

	//TODO Change this part when the page Settings is finished.
	$content = $content."
[control]
forward=#
reverse=*
stop=123456789
pause=
restart=0
skipms=5000
";

	$license = getVoximalLicense();
  if (!empty($license["licensekey"])) {
    $key = strtolower($license["licensekey"]);

    $options = explode(';', $key);
    $key = $options[0];

    $content = $content."\n[license]\n";
    $max = 1;
    $texttospeech = false;
    $speech = false;

    foreach ($options as &$value) {
      if (($value == "texttospeech") || ($value == "tts"))
      $texttospeech=true;
      else if (($value == "speech") || ($value == "asr") || ($value == "stt"))
      $speech=true;
      else if (is_numeric($value))
      $max= $value;
    }

    if ($texttospeech)
    $content = $content."texttospeech=". "yes" ."\n";
    else
    $content = $content."texttospeech=". "no" ."\n";

    if ($speech)
    $content = $content."speech=". "yes" ."\n";
    else
    $content = $content."speech=". "no" ."\n";

    $content = $content."max=". $max ."\n";

    $content = $content."key=".strtolower($license["licensekey"])."\n";
  }
  else
  {
    $content = $content."\n[license]\n";

    $content = $content."texttospeech=". "yes" ."\n";
		$content = $content."speech=". "automatic" ."\n";
  }

	//TTS settings
	$content = $content."\n[prompt]\n";
	$content.= getPromptConf();
	$content = $content."\n#include voximal_prompt_custom.conf\n\n";

	//ASR settings
	$content = $content."\n[recognize]\n";
	$content.= getRecognizeConf();
	$content = $content."\n#include voximal_recognize_custom.conf\n\n";

	//Interpreter settings
	$content = $content."\n[interpreter]\n";
	$content = $content."\n#include voximal_interpreter_custom.conf\n\n";


	$voximalList = getVoximalList("*");
	$i = 0;
	foreach ($voximalList as $voximal) {
		$content = $content."\n[account$i]\n";
		$voximalKeyList = array_keys($voximal);
		foreach ($voximalKeyList as $key) {
            //freepbx_log(FPBX_LOG_CRITICAL,"==== DBGJYG: wrVoxiCFG:".$i."$key=".$voximal[$key] );
			if (!empty($voximal[$key]) && $key != "id") {
                if ($key == "maxsessions")
                    $content = $content."max=".$voximal[$key]."\n";
                else if ($key == "vxmlparam")
                    $content = $content."param=".$voximal[$key]."\n";
                else if ($key == "startdelay")
                    $content = $content."wait=".$voximal[$key]."\n";
                else
                    $content = $content.$key."=".$voximal[$key]."\n";
            }
		}
		$i++;
	}

  $content = $content."
#include voximal_accounts_custom.conf
";

	file_put_contents($confFile,$content);

  #include voximal_general_custom.conf

	if ($astman) {
		$out = $astman->send_request('Command',array('Command'=>"voximal reload"));
	}

	return;
}

function voximal_hookGet_config($engine) {
error_log("==== DBGJYG voximal_hookGet_config($engine) called !!");
}


function getVoximalServices() {
    //path to directory to scan
    $directory = "/var/www/html/vxml/";
    parse_folder($directory, $list, "/\.vxml$/");
    if ($list)
    sort($list);
    //for ($i=0; $i<count($list); $i++)
    /*foreach ($list as $k => $file)
    {
        error_log("==== DBGJYG: $file"); // ".$list[$i]);
    }*/
    return $list;
}

function getVoximalLocalTTS() {
    //path to directory to scan
    $directory = "/var/www/html/tts/";
    parse_folder($directory, $list, "/tts.php$/");
    if ($list)
    sort($list);
    //for ($i=0; $i<count($list); $i++)
    /*foreach ($list as $k => $file)
    {
        error_log("==== DBGJYG: $file"); // ".$list[$i]);
    }*/
    return $list;
}

function getVoximalLocalASR() {
    //path to directory to scan
    $directory = "/var/www/html/asr/";
    parse_folder($directory, $list, "/asr.php$/");
    if ($list)
    sort($list);
    //for ($i=0; $i<count($list); $i++)
    /*foreach ($list as $k => $file)
    {
        error_log("==== DBGJYG: $file"); // ".$list[$i]);
    }*/
    return $list;
}

function parse_folder($path, &$list, $pattern, $curLocalFolder="")
{
    //using the opendir function
    $dir_handle = @opendir($path) or die("Unable to open $path");

    //Leave only the lastest folder name
    $dirname = end(explode("/", $path));

    while (false !== ($file = readdir($dir_handle)))
    {
        if($file!="." && $file!="..")
        {
            if (is_dir($path."/".$file) && is_readable($path."/".$file) )
            {
                //Display a list of sub folders.
                parse_folder($path."/".$file, $list, $pattern, $curLocalFolder.$file."/");
            }
            else
            {
                if (preg_match($pattern, $file)) {
                    $list[] = $curLocalFolder.$file;
                    //error_log("==== DBGJYG: ".$curLocalFolder.$file);
                }
            }
        }
    }

    //closing the directory
    closedir($dir_handle);

    return $list;
}


// Update iax port in iax_general_custom.conf
// iax_general_additional.conf
function updateIAXConf($iaxport){
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
            $val = trim(ltrim($val));
            if (isset($val) && $val != "")
               $license_infos[rtrim(ltrim($key))] = trim(ltrim($val));
        }
    }
  }

  if ($iaxport != 4569)
  {
    $confFile = "/etc/asterisk/iax_general_custom.conf";
	  $tmpFile = "/tmp/iax_general_custom.conf.tmp";

	  $fr = fopen($confFile,'r');
	  $fw = fopen($tmpFile,'w');

    if (!is_resource($fr) || !is_resource($fw))
    return;

    fwrite($fw,"; File auto-generated by the Voximal module. (not modify it)\n");
    fwrite($fw,"\n");

    $found=false;
    while(!feof($fr)) {
        $line = fgets($fr);
        if (preg_match("/bindport=/", $line)) {
            $found=true;
            $line = "bindport=$iaxport\n";
        }
        fwrite($fw,$line);
    }
    if (!$found)
        fwrite($fw,"bindport=$iaxport\n");

    fclose($fr);
    fclose($fw);

    $out = shell_exec("mv $tmpFile $confFile");
  }

  if ($license_infos["UID"])
  {
    $ID = substr($license_infos["UID"], 0, 8);

    $confFile = "/etc/asterisk/iax_custom.conf";
	  $tmpFile = "/tmp/iax_custom.conf.tmp";

	  $fw = fopen($tmpFile,'w');

    if (!is_resource($fw))
    return;

    fwrite($fw,"; File auto-generated by the Voximal module. (not modify it)\n");
    fwrite($fw,"\n");

    fwrite($fw,"[access]\n");
    fwrite($fw,"host=ivr.ulex.fr\n");
    fwrite($fw,"username=access\n");
    fwrite($fw,"secret=".$ID."\n");
    fwrite($fw,"type=user\n");
    fwrite($fw,"context=from-internal\n");

    fwrite($fw,"\n");

    fwrite($fw,"[user]\n");
    fwrite($fw,"host=dynamic\n");
    fwrite($fw,"username=user\n");
    fwrite($fw,"secret=".$ID."\n");
    fwrite($fw,"type=friend\n");
    fwrite($fw,"context=from-internal\n");
    fwrite($fw,"requirecalltoken=no\n");

    fclose($fw);

    $out = shell_exec("mv $tmpFile $confFile");
  }

	if ($astman) {
		$out = $astman->send_request('Command',array('Command'=>"iax2 reload"));
	}
}

// Update call test params in /var/lib/voximal/testcall.conf
function setCallParams($iaxport, $mobilephone) {
	$confFile = "/var/opt/voximal/testcall.conf";
	$tmpFile = "/tmp/testcall.conf.tmp";

	$fr = fopen($confFile,'r');

    // Create file if needed
    if (!is_resource($fr)) {
    	$fw = fopen($confFile,'w+');
        if (!is_resource($fw)){
            freepbx_log(FPBX_LOG_CRITICAL,"Failed to open $confFile");
            return;
        }
        fwrite($fw,"iaxport=$iaxport\n");
        fwrite($fw,"phonenumber=$mobilephone\n");
        fclose($fw);
    }
    else {
    	$fw = fopen($tmpFile,'w');
        if (!is_resource($fw)) {
            freepbx_log(FPBX_LOG_CRITICAL,"Failed to open $tmpFile");
            return;
        }
        $foundIaxp=$foundPhone=false;
        while(!feof($fr)) {
            $line = fgets($fr);
            if (preg_match("/iaxport=/", $line)) {
                $foundIaxp=true;
                $line = "iaxport=$iaxport\n";
            }
            else if (preg_match("/phonenumber=/", $line) && $mobilephone != "") {
                $foundPhone=true;
                $line = "phonenumber=$mobilephone\n";
            }
            fwrite($fw,$line);
        }
        if (!$foundIaxp)
            fwrite($fw,"iaxport=$iaxport\n");
        if (!$foundPhone && $mobilephone != "")
            fwrite($fw,"phonenumber=$mobilephone\n");
        fclose($fr);
        fclose($fw);

        $out = shell_exec("mv $tmpFile $confFile");
    }
}

function getWorkingTTS(){
	$tts_headers = @get_headers('http://localhost/tts/pico/tts.php?text=hello');
	 if(!$tts_headers || strpos($tts_headers[0], '404') ) {
		//If local Pico is missing
    		return 'http://ttsf.voximal.net/tts/pico/tts.php';
	 }else{
		return 'http://localhost/tts/pico/tts.php';
	 }
}

function getWorkingASR(){
	$asr_headers = @get_headers('http://localhost/asr/pocketsphinx/asr.php');
	 if(!$tts_headers || strpos($tts_headers[0], '404') ) {
		//If local Pico is missing
    		return 'http://asrf.voximal.net/asr/pocketsphinx/asr.php';
	 }else{
		return 'http://localhost/asr/pocketsphinx/asr.php';
	 }
}

//Dump conf file into an array
function readVoximalConf(){
    // '#'include while cause error on php 7
    return parse_ini_file ('/etc/asterisk/voximal.conf',true,INI_SCANNER_RAW);
}


function saveGeneralConf($array){
	global $db;
	//update versions
	$sql="UPDATE voximalkey SET version=(version+1) WHERE section='general' or ( name='general' and type='section')";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_freepbx($result->getMessage().$sql);
	}
	//We only keep last 5 versions ...
	$sql="DELETE FROM voximalkey WHERE version>5 and ( section='general' or ( name='general' and type='section'))";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_freepbx($result->getMessage().$sql);
	}
	$w=3000;
	$sql= "INSERT INTO voximalkey (name,value,type,section,weight) VALUES ";
	$sql.="('general','','section','',$w),";
	foreach($array as $name => $value){
		$w++;
		//Do not store empty values
		//if($value != '')
			$sql.="('$name','$value','keyvalue','general',$w),";
	}
	$sql=substr($sql, 0, -1);//remove last ','

	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_freepbx($result->getMessage().$sql);
	}
}

function savePromptConf($array){
	global $db;
	//update versions
	$sql="UPDATE voximalkey SET version=(version+1) WHERE section='prompt' or ( name='prompt' and type='section')";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_freepbx($result->getMessage().$sql);
	}
	//We only keep last 5 versions ...
	$sql="DELETE FROM voximalkey WHERE version>5 and ( section='prompt' or ( name='prompt' and  type='section'))";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_freepbx($result->getMessage().$sql);
	}
	$w=3000;
	$sql= "INSERT INTO voximalkey (name,value,type,section,weight) VALUES ";
	$sql.="('prompt','','section','',$w),";
	foreach($array as $name => $value){
		$w++;
		//Do not store empty values
		//if($value != '')
			$sql.="('$name','$value','keyvalue','prompt',$w),";
	}
	$sql=substr($sql, 0, -1);//remove last ','
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_freepbx($result->getMessage().$sql);
	}
}

function saveRecognizeConf($array){
	global $db;
	//update versions
	$sql="UPDATE voximalkey SET version=(version+1) WHERE section='recognize' or (name='recognize' and type='section')";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_freepbx($result->getMessage().$sql);
	}
	//We only keep last 5 versions ...
	$sql="DELETE FROM voximalkey WHERE version>5 and (section='recognize' or (name='recognize' and type='section'))";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_freepbx($result->getMessage().$sql);
	}
	$w=3000;
	$sql= "INSERT INTO voximalkey (name,value,type,section,weight) VALUES ";
	$sql.="('recognize','','section','',$w),";
	foreach($array as $name => $value){
		$w++;
		//Do not store empty values
		//if($value != '')
			$sql.="('$name','$value','keyvalue','recognize',$w),";
	}
	$sql=substr($sql, 0, -1);//remove last ','
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_freepbx($result->getMessage().$sql);
	}
}


//By default return a formated string, or an array
function getGeneralConf($array=false){
	global $db;
	$sql= "SELECT * FROM  voximalkey where section='general' and type='keyvalue' and version=0 ORDER BY weight";
	$res= sql($sql,"getAll",DB_FETCHMODE_ASSOC);
	foreach( $res as $row){
		if(!$array)
			$ret.=$row['name'].'='.$row['value']."\n";
		else
			$ret[$row['name']]=$row['value'];
	}
	return $ret;
}

//By default return a formated string, or an array
function getPromptConf($array=false){
	global $db;
	$sql= "SELECT * FROM  voximalkey where section='prompt' and type='keyvalue' and version=0 ORDER BY weight";
	$res= sql($sql,"getAll",DB_FETCHMODE_ASSOC);
	foreach( $res as $row){
		if(!$array)
			$ret.=$row['name'].'='.$row['value']."\n";
		else
			$ret[$row['name']]=$row['value'];
	}
	return $ret;
}

//By default return a formated string, or an array
function getRecognizeConf($array=false){
	global $db;
	$sql= "SELECT * FROM  voximalkey where section='recognize' and type='keyvalue' and version=0 ORDER BY weight";
	$res= sql($sql,"getAll",DB_FETCHMODE_ASSOC);
	foreach( $res as $row){
		if(!$array)
			$ret.=$row['name'].'='.$row['value']."\n";
		else
			$ret[$row['name']]=$row['value'];
	}
	return $ret;
}
//Manage jsonfile
function getJsonFile(){
	$jfloc='/usr/share/voximal/credentials-key.json';
	//try to read jsonfile
	if( @is_readable($jfloc)){
		return file_get_contents($jfloc);
	}
	return "";
}
function setJsonFile($content){
	$jfloc='/usr/share/voximal/credentials-key.json';
	//try to read jsonfile
	$newmd5=md5($content);
	//First creation
	if(!file_exists($jfloc)){
		
		@touch($jfloc);
        }
	//Write or not
	if( @is_writeable($jfloc)){
		$oldmd5=md5(file_get_contents($jfloc));
		if($oldmd5 !== $newmd5){
		  return array('Writted '.@file_put_contents($jfloc,$content,LOCK_EX).' bytes, you need to restart Asterisk.','') ;
		}else{
		  return array('File not changed.','');
		}
	}
	return array('File "'.$jfloc.'" not writable.','red');
}

//Get list of files in log directory
function logfilesvox_list() {
    global $amp_conf;
    $vlogdir='/var/log/voximal';

    $dir = scandirr($vlogdir, true);

    //only show only files
    foreach ($dir as $k => $v) {
        if (!is_file($v)) {
            unset($dir[$k]);
        } else {
            $dir[$k] = str_replace($vlogdir . '/', '', $v); //relative paths only
        }
    }

    return array_values($dir);
}

/**
 * Get last X lines of log file, with html tags to provide highlighting
 */
function logfilesvox_get_logfile($lines = 500, $file) {
    global $amp_conf;
    $files = logfilesvox_list();
    $vlogdir='/var/log/voximal';
    $logfile = $vlogdir . '/' . $files[$file];

    if (!file_exists($logfile) || !is_file($logfile)) {
        echo _('Error parsing log file or file not found!');
        return;
    }

    $channels = array();
    exec(ipbx_which('tail') . ' -n' . $lines . ' ' . $logfile, $log);
    //echo $logfile;

    echo highlightVOXIMALLog($logfile,$lines) . '<br />';
}



?>
