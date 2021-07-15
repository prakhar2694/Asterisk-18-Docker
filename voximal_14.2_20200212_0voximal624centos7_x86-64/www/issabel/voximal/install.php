<?php
/* FreePBX installer file
 * This file is run when the module is installed through module admin
 *
 * If this file returns false then the module will not install
 * EX:
 * return false;
 *
 */

global $db;
global $astman;
include_once __DIR__."/functions.inc.php";

/*
 * Sample of inserts
 * INSERT INTO `voximal` VALUES (1,'helloworld','http://localhost/vxml/helloworld.vxml','0',NULL,NULL,NULL,NULL,'200','automatic',NULL);
 * INSERT INTO `voximallicense` VALUES (1,1,'No','No','No','','No','','','00','');
 * INSERT INTO `voximalconfiguration` VALUES ('configuration','Yes',256,'pcm','Disabled','','Yes','',60);
 * INSERT INTO `voximaltts` VALUES ('HTTP',1,'http://localhost/tts/flite/tts.php',NULL,NULL,NULL,NULL,-1,0,NULL),('MRCP',0,NULL,NULL,5061,NULL,NULL,-1,0,0);
 * INSERT INTO `incoming` VALUES ('','8881','app-voximal-1,s,1',NULL,NULL,NULL,NULL,0,'','','default','HelloWorld','',0,'','3','10','');*
 
 */

//IssabelPBX special link for assests : needed for embeded
//https://github.com/IssabelFoundation/issabelPBX/blob/master/build/issabelpbx.spec
if ( ! is_dir('/var/www/html/modules/voximal')) {
    mkdir('/var/www/html/modules/voximal',0777);
    out(_("voximal dir created : /var/www/html/modules/voximal"));
}
symlink('/var/www/html/admin/modules/voximal/assets','/var/www/html/modules/voximal/assets');
symlink('/var/www/html/admin/modules/voximal/images','/var/www/html/modules/voximal/images');





//Read voximal.conf
$confArray=readVoximalConf();

$sql = "CREATE TABLE IF NOT EXISTS voximal (
			id				INTEGER 	 NOT NULL PRIMARY KEY auto_increment,
			name 			VARCHAR(100) NOT NULL UNIQUE,
			url 			VARCHAR(300) NOT NULL,
			maxsessions 	VARCHAR(10),
			dialformat		VARCHAR(300),
			mark			VARCHAR(300),
			maxtime			VARCHAR(10),
			vxmlparam		VARCHAR(300),
			startdelay		VARCHAR(10),
			speech			VARCHAR(50),
			speechprovider	VARCHAR(100)
		)";

$check = $db->query($sql);
if(DB::IsError($check)) {
  //Upgrade table
  //
  //Remove goto unsed col
  $check = $db->query("SHOW COLUMNS FROM `voximal` LIKE 'goto'");
  if(!DB::IsError($check)) {
    $check = $db->query("ALTER TABLE `voximal` DROP COLUMN `goto`");
    if(!DB::IsError($check))
      out(_("Goto col removed"));
  }
  out(_("Voximal module already installed"));
}else{
    /* Insert configured accounts */
    if ($astman) {
    	$out = $astman->send_request('Command',array('Command'=>"voximal show accounts"));
    	$out = explode("\n",$out['data']);
    }
/* Parse:
Account 0
 Name             : HelloWord
 Number           :
 URL              : http://localhost/vxml/helloword.vxml
 Param            :
 Mark             :
 Max              : 2 or unlimited
 Count            : 0
 Counter          : 6
*/
    out(_("Adding accounts..."));
    $newAccount = 0;
    foreach ($out as $line) {
    	if ( (strpos($line, "No such command") !== false ) || (strpos($line, "No accounts configured") !== false ) ) {
    		$exists = 0;
        out(_("Unable to get accounts from app_voximal"));
    		break;
    	}
        //out(_("treat : $line"));
        // Check if line is empty
        if (trim($line) != "") {
            // check if account desc
            if (strpos($line, " : ") !== false) {
                $elts = explode(" : ", $line);
                $key  = trim($elts[0]);
                $value= trim($elts[1]);
                if ($key == "Name") $name = trim($value);
                else if ($key == "Max") { 
                  $max = trim($value);
                  if($max == 'unlimited') $max = "0";
                }
                else if ($key == "URL") $url = trim($value);
            }
            else if (preg_match("/^Account/",$line)) {
                if ($newAccount) {
                    $sql = "INSERT INTO voximal (name,url,maxsessions,speech) VALUES ('".
                					$name."','".
                					$url."','".
                					$max."','automatic')";
                	$result = $db->query($sql);
                	if(DB::IsError($result)) {
                		//die_freepbx($result->getMessage().$sql);
                        out(_(" - account \"$name\" already configured"));
                	}
                    else {
                        out(_(" - account \"$name\" added"));
                    }
                }
                else {
                    $newAccount = 1;
                }
            }
        }
    }
    // Add last one
    if ($newAccount) {
        // Detect new account => insert previous in base
        $sql = "INSERT INTO voximal (name,url,maxsessions,speech) VALUES ('".
    					$name."','".
    					$url."','".
    					$max."','automatic')";
    	$result = $db->query($sql);
    	if(DB::IsError($result)) {
    		//die_freepbx($result->getMessage().$sql);
            out(_(" - account \"$name\" already configured"));
    	}
        else {
            out(_(" - account \"$name\" added"));
        }
    }

    #Update sample helloworld if present
    $nb=$db->getOne("SELECT count(*) from `voximal` WHERE name='helloworld';");
    $res=$db->query("INSERT INTO `voximal` VALUES 
      (1,'helloworld','http://localhost/vxml/helloworld.vxml','0',NULL,NULL,NULL,NULL,'200','automatic',NULL)
      ON DUPLICATE KEY UPDATE url='http://localhost/vxml/helloworld.vxml',startdelay='200';");
    
    if (!DB::IsError($res) && $nb <= 1 ){

      $exten8965 = $db->getOne("SELECT count(*) from `incoming` WHERE extension='8965';");
      if (!DB::IsError($exten8965) && $exten8965 == 0 )
      {
        $sql1="DELETE FROM `incoming` WHERE extension='8965';";
        $sql2="INSERT INTO `incoming` VALUES
        ('','8965','app-voximal-1,s,1',NULL,NULL,NULL,NULL,0,'','','default','HelloWorld','',0,'','3','10','');";
        $result1 = $db->query($sql1);
        $result2 = $db->query($sql2);
        if(!DB::IsError($result1) && !DB::IsError($result2)) {
          out(_(" - Extension 8965 added"));
        }else{
  	      //out(_( $result1->getMessage()));
  	      //out(_( $result2->getMessage()));
          out(_("Error adding extension 8965"));
        }
      }else{
        out(_("Extension 8965 already set"));
      }
    }else{
      out(_("Not installing helloworld sample"));
    }

    #Update sample parrot if present
    $nb=$db->getOne("SELECT count(*) from `voximal` WHERE name='parrot';");
    $res=$db->query("INSERT INTO `voximal` VALUES 
      (2,'parrot','http://localhost/vxml/parrot.vxml','0',NULL,NULL,NULL,NULL,'200','automatic',NULL)
      ON DUPLICATE KEY UPDATE url='http://localhost/vxml/parrot.vxml',startdelay='200';");
    if (!DB::IsError($res) && $nb <= 1 ){
      $exten8966 = $db->getOne("SELECT count(*) from `incoming` WHERE extension='8966';");
      if (!DB::IsError($exten8966) && $exten8966 == 0 )
      {
        $sql="INSERT INTO `incoming` VALUES
        ('','8966','app-voximal-2,s,1',NULL,NULL,NULL,NULL,0,'','','default','Parrot','',0,'','3','10','');";
        $result = $db->query($sql);
        if(!DB::IsError($result)) {
          out(_(" - Extension 8966 added"));
        }else{
  	      //out(_( $result->getMessage()));
          out(_("Error adding extension 8966"));
        }
      }else{
        out(_("Extension 8966 already set"));
      }
    }else{
      out(_("Not installing parrot sample"));
    }
}


/*
 * Create license table
 */
$sql = "CREATE TABLE IF NOT EXISTS voximallicense (
			id				INTEGER 	 NOT NULL PRIMARY KEY auto_increment,
			licensekey		VARCHAR(200) NOT NULL
		)";

$check = $db->query($sql);
if(DB::IsError($check)) {
	////die_freepbx("Can not create voximallicense table");
	//die_freepbx($check->getMessage().$sql);
    out(_("Voximal license already installed"));
}
else {
    if ($astman) {
    	$out = $astman->send_request('Command',array('Command'=>"voximal show license"));
    	$out = explode("\n",$out['data']);
    }

    $exists = 1;
    foreach ($out as $line) {
    	if (strpos($line, "No such command 'voximal show license'") !== false) {
    		$exists = 0;
            out(_("Voximal license manager not found"));
    		break;
    	}
    }

    $sql = "SELECT * FROM voximallicense";
    $results = $db->getAll($sql,DB_FETCHMODE_ASSOC);
    if ($exists && empty($results)) {
    	$license = array();
    	if (strpos($out[0],"Privilege") !== false) {
    		unset($out[0]);
    		$out = array_values($out);
    	}
    	foreach ($out as $param) {
    		if (empty($param)) continue;
    		$name = ltrim(rtrim(substr($param,0,strpos($param,":") - 1)));
    		$value = substr($param,strpos($param,":") + 1);
    		$license[$name] = $value;
    	}
    	$dialer = ucfirst(trim(shell_exec("grep dialer /etc/asterisk/voximal.conf | cut -d'=' -f2")));
    	if (empty($dialer)) $dialer = "No";
    	if (isset($license['Expiration'])) {
    		$partial = explode(' ', $license['Expiration']);
    		$partial = explode('/',trim($partial[2]));
    		$expiration = substr($partial[0], -2).$partial[1].$partial[2];
    	} else {
    		$expiration = "";
    	}
    	$sql = "INSERT INTO voximallicense (licensekey) VALUES ('".
    					trim($license['Key'])."')";
    	$result = $db->query($sql);
    	if(DB::IsError($result)) {
    		die_freepbx($result->getMessage().$sql);
    	}
        out(_("Voximal license updated"));
    }
}

/*
 * Create configuration table
 */
$sql = "CREATE TABLE IF NOT EXISTS voximalkey (
		name	VARCHAR(255) NOT NULL,
		value	VARCHAR(255),
		type    ENUM('keyvalue','section','include'),
		section VARCHAR(255),
		weight  INT DEFAULT 0,
		version INT DEFAULT 0)
	";
		
$check = $db->query($sql);
if(DB::IsError($check)) {
	//die_freepbx("Can not create tts table");
    out(_("Voximal configuration already installed"));
}
else
{
  if(array_key_exists('general',$confArray)){
    //We used this old params

    //print_r($confArray);

    saveGeneralConf($confArray['general']);
    out(_("Voximal general configuration parsed"));
  }else{
    $general['autoanswer']='yes';
    $general['recordsilence']='yes';
    $general['threshold']='256';
    $general['wavdefaultcodec']='pcm';
    $general['debug']='no';
    $general['monitordirectory']='@freepbx';
    $general['monitor']='no';
    $general['priorityevents']='disabled';
    $general['dialformat']='SIP/%s';
    $general['speechprovider']='';
    $general['speechbeeps']='no';
    saveGeneralConf($general);
    out(_("Voximal general default created "));
  }

  if(array_key_exists('prompt',$confArray)){
    //We used this old params
    savePromptConf($confArray['prompt']);    
    out(_("Voximal TTS configuration parsed"));
  }else{
    $prompt['uri']=getWorkingTTS();
    $prompt['method']='POST';
    $prompt['format']='wav';
    $prompt['ssml']='0';
    $prompt['cutprompt']='0';
    $prompt['maxage']='-1';
    savePromptConf($prompt);
    out(_("Voximal TTS default created "));
  }
}

//default admin sections are :
//sec="voximalapp;voximalsettings;voximallogfiles;voximalstatistics;voximalstatus;";

