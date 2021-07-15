<?php
// File    : editor.voximalapp.php
// Version : $Revision: 1.3 $


//Check if user is "logged in"
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

//Make it simple : TODO add WEBDIR//URL for editor to settings
$WEBDIR='/var/www/html/vxml';

?>

<!-- <h2>Voximal Editor</h2>  -->

<?php
/**
 * Output result of Vxmlvalidator cmd
 *  In case of valid script => line is  : VALID: <file>
 *  In case of errors :
 *          INVALID: /tmp/tmpEditedFile
 *          Info: DocumentParser::FetchDocument - Loading grammar: http://www.w3.org/TR/voicexml21/vxml.xsd
 *          Error (module='com.speechworks.vxi' id='990') : URL='/tmp/tmpEditedFile'
 *          Info: DocumentParser::FetchDocument - Parse error in file "/tmp/tmpEditedFile", line 15, column 5 - Expected end of tag 'prompt'
 *
 *          TIME PROCESSING: 24359
 *
 *          PAGES PARSED: 1 (41.0526 p/s)
 *
 */
function parseVxmlValidatorResult($array_result) {
    $retval="";
    $startCheck=false;
    foreach ($array_result as $line) {
        if (preg_match("/^VALID/", $line)) {
            $retval = "VoiceXML syntax is valid.";
            break;
        }
        else if (preg_match("/^INVALID/", $line)) {
            $startCheck=true;
            $retval = "INVALID VoiceXML syntax :<br>";
            continue;
        }
        else if (preg_match("/^TIME PROCESSING/", $line))
            break;

        if ($startCheck) {
            //$line = str_replace("/tmp/tmpCheckedFile", "edited file", $line);
            if (!(preg_match("/Loading grammar/", $line) || preg_match("/com.speechworks.vxi/", $line))) {
                $line = str_replace("Info: DocumentParser::FetchDocument - ", "", $line);
                $line = str_replace('in file "/tmp/tmpCheckedFile", ', ": ", $line);
                $retval .= $line . "<br>";
            }
        }
    }
    return $retval;
}


//echo ("<br> dir = '".$dir."'<hr>");

if( ! $dir=realpath($WEBDIR.'/'.$_GET['dir'])){
  $dir=realpath($WEBDIR);
}else{
  //please Do not go upper than $WEBDIR
  if(substr($dir, 0, strlen($WEBDIR)) != $WEBDIR)
    $dir=realpath($WEBDIR);
}

$formurl = "&view=editor";
$formurlRenameDir = "&view=editor";
if ($dir != $WEBDIR) {
	$formurl = "&view=editor&dir=".$_GET['dir'];
	$formurlRenameDir = "&view=editor&dir=".dirname($_GET['dir']);
}

/* debug ...
print_r($_GET);echo 'GET<hr>';
print_r($_POST);echo 'POST<hr>';
echo ("<br> dir = '".$dir."'<hr>");
*/

# Rename File
if (isset($_POST['oldname']) && isset($_POST['newname'])) {

	//print_r("OLD: ".$_POST['oldname']."<br>");
	//print_r("NEW: ".dirname($_POST['oldname'])."/".$_POST['newname']."<br>");
	exec("mv ".$_POST['oldname']." ".dirname($_POST['oldname'])."/".$_POST['newname']);

}

# Upload File
if (isset($_FILES['uploadedfiles'])) {

	//print_r($_FILES['uploadedfiles']);
	$i = 0;
	foreach ($_FILES['uploadedfiles']['name'] as $filename) {

		exec("mv $dir/$filename $dir/$filename.bak");
		exec("mv ".$_FILES['uploadedfiles']['tmp_name'][$i++]." $dir/$filename");
		exec("chown asterisk. $dir/$filename");

	}

}

# Remove File or Dir (empty)
if (isset($_GET['delete'])) {
  //please do not delete  upper than $WEBDIR
  $todelete=realpath("$dir/".$_GET['delete']);
  if(substr($todelete, 0, strlen($WEBDIR)) === $WEBDIR){
    if( is_dir($todelete) === true ){
     @rmdir($todelete);
     //echo "<hr> rmdir $todelete <hr>";
    }else{
     @unlink($todelete);
     //echo "<hr> unlink $todelete <hr>";
    }
  }
}

# Create folder
if (isset($_POST['newfoldername'])) {

	exec("mkdir -p $dir/".$_POST['newfoldername']);
	exec("chown -R asterisk. $dir/".$_POST['newfoldername']);

}

# Create file
if (isset($_POST['newfilename'])) {

	exec("echo '' > $dir/".$_POST['newfilename']);
	exec("chown asterisk. $dir/".$_POST['newfilename']);

}

# Saving file or return of vxml check
$mVxmlValidation = false;
if (isset($_POST['editedFile'])) {
    if (isset($_REQUEST["checkVxml"]) && $_REQUEST["checkVxml"]=="true") {
        file_put_contents("/tmp/tmpCheckedFile",ltrim($_POST['code']));
        exec("/usr/sbin/voximalv /tmp/tmpCheckedFile", $output, $mVxmlValidationResultCode);
        ///echo "Validator tmp file! retexec='$retval' <br>";
        //print_r($output) ;
        $mVxmlValidation = true;
        if ($mVxmlValidationResultCode==0) {
            $mVxmlValidationResult = "The Vxml syntax is valid";
        }
        else {
            $mVxmlValidationResult = parseVxmlValidatorResult($output);
        }
    }
	else {
        file_put_contents("/tmp/tmpEditedFile",ltrim($_POST['code']));
//error_log("/tmp/tmpEditedFile created ");
        exec("mv ".$_POST['editedFile']." ".$_POST['editedFile'].".bak");
        exec("mv /tmp/tmpEditedFile ".$_POST['editedFile']);
//error_log("/tmp/tmpEditedFile => ".$_POST['editedFile']);
	    exec("chown asterisk. ".$_POST['editedFile']);
//error_log("chown asterisk. ".$_POST['editedFile']) ;
        //echo "save file to " .$_POST['editedFile'];
    }

}

if (isset($_POST['getfile']) || isset($_POST['editedFile'])) {
//if (isset($_POST['getfile'])) {
    if (isset($_POST['getfile']) && $_POST['getfile'] != "undefined" ) {
        $filename = $_POST['getfile'];
    }
    else {
        $filename = $_POST['editedFile'];
    }
	$exten = substr( $filename, strpos($filename,".") + 1);
	switch($exten) {
		case "sh":
			$mode = "shell";
			break;
		case "xml":
			$mode = "xml";
			break;
		case "vxml":
			$mode = "xml";
			break;
		case "aiml":
			$mode = "xml";
			break;
		case "grxml":
			$mode = "xml";
			break;
		case "gram":
			$mode = "xml";
			break;
		case "srgs":
			$mode = "xml";
			break;
		case "js":
			$mode = "javascript";
			break;
		case "php":
			$mode = "application/x-httpd-php";
			break;
		default:
			$mode = $exten;
			break;
	}
//error_log("mode = $mode") ;
}

?>

<?php //if (!isset($_POST['getfile']) && !isset($_POST['editedFile'])) {?>

<script type="text/javascript">

function showRename() {
	$("#rename").toggle();
}

</script>
<?php if ((!isset($_POST['getfile'])) && (!isset($_POST['editedFile'])) && (!isset($_POST['soundfile']))) {?>
<?php echo load_view(dirname(__FILE__) . '/rnav.voximaleditor.php');?>

<script src="modules/voximal/assets/sorttable.js"></script>
<br>
<?php 
  //Only display rename on subdirs
  if( $dir!= $WEBDIR) {
?>
<span style="font-weight: bold; color: gray;"><?php echo _("Directory :")?> </span><span><?php echo str_replace("/"," / ",$dir) ?></span><img style="width: 16px; height: 16px; margin-left: 5px; margin-bottom: 6px; cursor: pointer;" onclick="showRename()" onmouseover='' src="/admin/modules/voximal/images/edit.png">
<form action="config.php?display=voximalapp<?php echo $formurlRenameDir?>" style="display: none" method="post" id="rename">
	<br>
	<input type="hidden" name="oldname" value="<?php echo $dir?>">
	<input type="hidden" name="view" value="editor">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo _("Insert new name:")?> <input type="text" name="newname" value="">&nbsp;<button type="button" onclick="rename()"><?php echo _("Rename")?></button>&nbsp;<span style="color: red" id="errRename"></span>
</form>
<br><br>
<?php }else{?>
<span style="font-weight: bold; color: gray;"><?php echo _("Directory:")?> </span><span><?php echo str_replace("/"," / ",$dir) ?></span>
<br><br>
<?php }?>
<style>

/* This avoid ugly focus on thead y tbody colors du to jquery embeding */

.ui-state-focus {
  background: none !important;
  background-color: none  !important;
  border: none !important;
}

</style>
<div id="foldercontent">
	<table class="sortable" style="width: 500px">
      	<thead>
        	<tr>
        		<td></td>
          		<th><?php echo _("Filename")?></th>
          		<th><?php echo _("Type")?></th>
          		<th><?php echo _("Size <small>(bytes)</small>")?></th>
          		<th><?php echo _("Date Modified")?></th>
        	</tr>
     	</thead>
     	<tbody>
     		<?php
	        // Opens directory
	        $myDirectory=opendir($dir);
	        if ($dir != $WEBDIR) {
			//Create  parent dir links
	        	$requestedDir = realpath($dir.'/..');
	          	$requestedDir = str_replace($WEBDIR,"",$requestedDir);
	        	print("
     				<tr class='$class'>
     				<td colspan='2'><a href='config.php?display=voximalapp&view=editor&dir=$requestedDir'><b>&#8617;&nbsp; .. "._("Parent")."</b></a></td>
     				<td><a href='config.php?display=voximalapp&view=editor&dir=$requestedDir'><b>&lt;Directory&gt;</b></a></td>
     				<td><a href='config.php?display=voximalapp&view=editor&dir=$requestedDir'></a></td>
     				<td sorttable_customkey='$timekey'><a href='config.php?display=voximalapp&view=editor&dir=$requestedDir'></a></td>
     			</tr>");
	        }
	        // Gets each entry
	        while($entryName=readdir($myDirectory)) {
	        	$dirArray[]=$entryName;
	        }

	        // Finds extensions of files
	        function findexts ($filename) {
	          	$filename=strtolower($filename);
	          	$exts=explode(".", $filename);
	          	$n=count($exts)-1;
	          	$exts=$exts[$n];
	          	return $exts;
	        }

	        // Closes directory
	        closedir($myDirectory);

	        // Counts elements in array
	        $indexCount=count($dirArray);

	        // Sorts files
	        function sortDir ($a, $b) {
                global $WEBDIR;

	        	$dir = isset($_GET['dir']) ? realpath($WEBDIR.$_GET['dir']) : realpath($WEBDIR);

	        	if (is_dir("$dir/$a") && is_dir("$dir/$b")) {

	        		return strcasecmp($a,$b);

	        	} elseif (is_file("$dir/$a") && is_dir("$dir/$b")) {

	        		return 1;

	        	} elseif (is_file("$dir/$b") && is_dir("$dir/$a")) {

	        		return -1;

	        	} elseif (is_file("$dir/$a") && is_file("$dir/$b")) {

	        		return strcasecmp($a,$b);

	        	}

	        }


	        usort($dirArray, "sortDir");

	        // Loops through the array of files
	        for($index=0; $index < $indexCount; $index++) {

	          // Allows ./?hidden to show hidden files
	          	if($_SERVER['QUERY_STRING']=="hidden") {
	          		$hide="";
	          		$ahref="./";
	          		$atext="Hide";
	          	} else {
	          		$hide=".";
	          		$ahref="./?hidden";
	          		$atext="Show";
	          	}
	          	if(substr("$dirArray[$index]", 0, 1) != $hide) {

	          	// Gets File Names
	          	$name=$dirArray[$index];
	          	$namehref=$dirArray[$index];

	          	// Gets Extensions
	          	$extn=findexts($dirArray[$index]);

	          	// Gets file size
	          	$size=number_format(filesize("$dir/".$dirArray[$index]));

	          	// Gets Date Modified Data
	          	$modtime=date("M j Y g:i A", filemtime("$dir/".$dirArray[$index]));
	          	$timekey=date("YmdHis", filemtime("$dir/".$dirArray[$index]));

	          	// Pretifies File Types, add more to suit your needs.
	          	$edit = 0;
	          	$play = 0;
	          	//$video = 0;
	          	switch ($extn){
	            	case "png": $extn="PNG Image"; break;
	            	case "jpg": $extn="JPEG Image"; break;
	            	case "svg": $extn="SVG Image"; break;
	            	case "gif": $extn="GIF Image"; break;
	            	case "ico": $extn="Windows Icon"; break;

		            case "txt": $extn="Text File"; $edit = 1; break;
		            case "log": $extn="Log File"; $edit = 1; break;
	    	        case "html": $extn="HTML File"; $edit = 1; break;
	        	    case "php": $extn="PHP Script"; $edit = 1; break;
	            	case "js": $extn="Javascript"; $edit = 1; break;
	            	case "css": $extn="Stylesheet"; $edit = 1; break;
	            	case "conf": $extn="Configuration File"; $edit = 1; break;
	            	case "xml": $extn="XML File"; $edit = 1; break;
	            	case "vxml": $extn="VoiceXML File"; $edit = 1; break;
	            	case "grxml": $extn="Grammar File"; $edit = 1; break;
	            	case "gram": $extn="Grammar File"; $edit = 1; break;
	            	case "srgs": $extn="Grammar File"; $edit = 1; break;
	            	case "aiml": $extn="AIML File"; $edit = 1; break;
	            	case "sh": $extn="Script File"; $edit = 1; break;

	            	case "pdf": $extn="PDF Document"; break;

	            	case "zip": $extn="ZIP Archive"; break;
	            	case "bak": $extn="Backup File"; break;

	            	case "wav": $extn="WAV File"; $play = 1; break;
	            	case "gsm": $extn="GSM File"; $play = 1; break;
	            	case "mp3": $extn="MP3 File"; $play = 1; break;

	            	//case "mp4": $extn="MP4 File"; $video = 1; break;
	            	//case "h263": $extn="H263 File"; $video = 1; break;

	            	default: $extn=strtoupper($extn)." File"; break;
	          	}

	          	// Separates directories
	          	if(is_dir("$dir/".$dirArray[$index])) {
	            	$extn="&lt;Directory&gt;";
	            	$size="&lt;Directory&gt;";
	            	$class="dir";
	          	} else {
	            	$class="file";
	          	}
			//Create direectory links
	          	if ( $dir == $WEBDIR ) {
	          		$currentDir = "/";
	          	} else {
	          		$currentDir = str_replace($WEBDIR,"",$dir);
	          		$currentDir = $currentDir."/";
	          	}
			if(is_dir("$dir/".$dirArray[$index])) {
		          	print("
		          		<tr class='$class'>
     					<td><img height='16' width='16' onmouseover='' style='cursor: pointer;' onclick='confirmation(\"$name\",\"directory\")' src='modules/voximal/images/trash.png'></td>
		            	<td><a href='config.php?display=voximalapp&view=editor&dir=$currentDir$name'><b>$name</b></a></td>
		            	<td><a href='config.php?display=voximalapp&view=editor&dir=$currentDir$name'><b>$extn</b></a></td>
		            	<td><a href='config.php?display=voximalapp&view=editor&dir=$currentDir$name'><b></b></a></td>
		            	<td sorttable_customkey='$timekey'><a href='config.php?display=voximalapp&view=editor&dir=$currentDir$name'><b>$modtime</b></a></td>
		          	</tr>");
			} else {
					if ($edit) {
						print("
							<tr class='$class'>
     						<td><img height='16' width='16' onmouseover='' style='cursor: pointer;' onclick='confirmation(\"$name\",\"file\")' src='modules/voximal/images/trash.png'></td>
							<td class='edit' edit='$dir/$name' onmouseover='' style='cursor: pointer;'>&nbsp;$name</td>
							<td class='edit' edit='$dir/$name' onmouseover='' style='cursor: pointer;'>&nbsp;$extn</td>
							<td class='edit' edit='$dir/$name' onmouseover='' style='cursor: pointer;'>&nbsp;$size</td>
							<td sorttable_customkey='$timekey' class='edit' edit='$dir/$name' onmouseover='' style='cursor: pointer;'>&nbsp;$modtime</td>
						</tr>");
					} elseif ($play) {
						print("
							<tr class='$class'>
							<td><img height='16' width='16' onmouseover='' style='cursor: pointer;' onclick='confirmation(\"$name\",\"file\")' src='modules/voximal/images/trash.png'></td>
							<td class='play' play='$dir/$name' onmouseover='' style='cursor: pointer;'>&nbsp;$name</td>
							<td class='play' play='$dir/$name' onmouseover='' style='cursor: pointer;'>&nbsp;$extn</td>
							<td class='play' play='$dir/$name' onmouseover='' style='cursor: pointer;'>&nbsp;$size</td>
							<td sorttable_customkey='$timekey' class='play' play='$dir/$name' onmouseover='' style='cursor: pointer;'>&nbsp;$modtime</td>
						</tr>");
					} else {
						print("
							<tr class='$class'>
     						<td><img height='16' width='16' onmouseover='' style='cursor: pointer;' onclick='confirmation(\"$name\",\"file\")' src='modules/voximal/images/trash.png'></td>
							<td>&nbsp;$name</td>
							<td>&nbsp;$extn</td>
							<td>&nbsp;$size</td>
							<td sorttable_customkey='$timekey'>&nbsp;$modtime</td>
						</tr>");
					}
			}
	         }
	      }
	      ?>
     	</tbody>
     </table>
     <br>
     <form id="newfile" style="display: none" action="config.php?display=voximalapp<?php echo $formurl?>" method="post">
        <input type="hidden" name="view" value="editor">
	     <table style="width: 500px">
	     	<tr><td style="width: 180px;"><?php echo _("Name for the new file")?>: </td><td style="width: 150px;"><input type="text" name="newfilename"></td><td><input type="submit" style="float: left" value="<?php echo _("create")?>"></td></tr>
	     	<tr><td colspan="3"><hr></td></tr>
	     </table>
     </form>
     <form id="newfolder" style="display: none" action="config.php?display=voximalapp<?php echo $formurl?>" method="post">
        <input type="hidden" name="view" value="editor">
	     <table style="width: 500px">
	     	<tr><td style="width: 180px;"><?php echo _("Name for the new folder")?>: </td><td style="width: 150px;"><input type="text" name="newfoldername"></td><td><input type="submit" style="float: left" value="<?php echo _("create")?>"></td></tr>
	     	<tr><td colspan="3"><hr></td></tr>
	     </table>
     </form>
     <br>
     <form id="upload" action="config.php?display=voximalapp<?php echo $formurl?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="view" value="editor">
     	<table>
     		<tr><td colspan="2"><?php echo _("Upload files to current directory")?></td></tr>
     		<tr><td><?php echo _("Select files to upload")?>: </td><td><input type="file" name="uploadedfiles[]" id="uploadedfiles" multiple></td></tr>
     		<tr><td colspan="2"><input type="submit" value="<?php echo _("Upload")?>"></td></tr>
     	</table>
     </form>
</div>

<script type="text/javascript">

	function deploy(id) {

		if (id == "newfile") $("#newfolder").hide();
		else if (id == "newfolder") $("#newfile").hide();
		$("#" + id).show();

	}

	function confirmation(item,type) {
		
		var confirmation = confirm("<?php echo _("Do you really want to delete this")?> " + type + " : " + item  +"?");
		if (confirmation) {
			//this do not works in embedded issabel...
			window.location.href = "config.php?display=voximalapp&<?php echo $formurl?>&delete=" + item;
		}

	}



</script>
<?php }?>




<br><br>

<?php //if (isset($_POST['getfile']) || isset($_POST['editedFile'])) {?>
<?php if ((isset($_POST['getfile']) || isset($_POST['editedFile'])) && !isset($_POST['soundfile'])) {?>

<script type="text/javascript">
	$(function() {
		var link = document.createElement( "link" );
		link.href = "modules/voximal/assets/codemirror/lib/codemirror.css";
		link.type = "text/css";
		link.rel = "stylesheet";
		link.media = "screen,print";
		document.getElementsByTagName( "head" )[0].appendChild( link );
		var link2 = document.createElement( "link" );
		link2.href = "modules/voximal/assets/codemirror/addon/hint/show-hint.css";
		link2.type = "text/css";
		link2.rel = "stylesheet";
		link2.media = "screen,print";
		document.getElementsByTagName( "head" )[0].appendChild( link2 );
	});
</script>
<script src="modules/voximal/assets/codemirror/lib/codemirror.js"></script>
<script src="modules/voximal/assets/codemirror/mode/xml/xml.js"></script>
<script src="modules/voximal/assets/codemirror/addon/hint/show-hint.js"></script>
<?php /*<script src="modules/voximal/assets/codemirror/addon/hint/anyword-hint.js"></script>*/ ?>
<script src="modules/voximal/assets/codemirror/addon/selection/active-line.js"></script>
<script src="modules/voximal/assets/codemirror/addon/edit/closebrackets.js"></script>
<script src="modules/voximal/assets/codemirror/addon/edit/closetag.js"></script>
<script src="modules/voximal/assets/codemirror/addon/fold/xml-fold.js"></script>
<script src="modules/voximal/assets/codemirror/addon/edit/matchtags.js"></script>
<?php if ($mode == "application/x-httpd-php") {?>
<script src="modules/voximal/assets/codemirror/mode/php/php.js"></script>
<script src="modules/voximal/assets/codemirror/mode/css/css.js"></script>
<script src="modules/voximal/assets/codemirror/mode/htmlmixed/htmlmixed.js"></script>
<script src="modules/voximal/assets/codemirror/mode/javascript/javascript.js"></script>
<script src="modules/vxml/assets/codemirror/mode/javascript/javascript.js"></script>
<script src="modules/vxml/assets/codemirror/mode/clike/clike.js"></script>
<?php } elseif ($mode == "javascript") {?>
<script src="modules/voximal/assets/codemirror/mode/javascript/javascript.js"></script>
<?php }?>

<div id="editor">
	<?php
    if (isset($_POST['getfile']) && $_POST['getfile'] != "undefined" ) {
        $filename = $_POST['getfile'];
    }
    else {
        $filename = $_POST['editedFile'];
    }
    $fileToEdit = str_replace("/"," / ",str_replace($WEBDIR,"",$filename))?>
	<span style="font-weight: bold; color: gray;"><?php echo _("Editing file")?>: </span><span><?php echo $fileToEdit ?></span><img style="width: 16px; height: 16px; margin-left: 5px; margin-bottom: 6px; cursor: pointer;" onclick="showRename()" onmouseover='' src="modules/voximal/images/edit.png">
	<form action="config.php?display=voximalapp<?php echo $formurl?>" style="display: none" method="post" id="rename">
        <input type="hidden" name="view" value="editor">
		<br>
		<input type="hidden" name="oldname" value="<?php echo $_POST['getfile']?>">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo _("Insert new name")?>: <input type="text" name="newname" value="">&nbsp;<button type="button" onclick="rename()"><?php echo _("Rename")?></button>&nbsp;<span style="color: red" id="errRename"></span>
	</form>
	<br><br>
	<form action="config.php?display=voximalapp<?php echo $formurl?>" method="post" id="applyChanges" onsubmit="disableBeforeUnload();">
        <input type="hidden" name="view" value="editor">
  		<input type="hidden" name="checkVxml" value="false">
		<input type="hidden" name="editedFile" value="<?php echo isset($_POST['getfile']) ? $_POST['getfile'] : $_POST['editedFile']?>">
		<input type="hidden" name="getfile" value="<?php echo isset($_POST['getfile']) ? $_POST['getfile'] : $_POST['editedFile']?>">
		<textarea id="code" name="code"><?php
                if ($mVxmlValidation)
                    $f = fopen("/tmp/tmpCheckedFile",'r');
                else
				    $f = fopen($filename,'r');
				while(!feof($f)) {
					$line = fgets($f);
					if (strpos($line,"<?xml") !== false) $line = ltrim($line);
					echo "$line";
				}
				fclose($f);
			?></textarea>
		<br>
		<input type="submit" value="<?php echo _("Save")?>"/>
    <input type="submit" onclick="discard()" value="<?php echo _("Undo")?>"/>
    <input type="button" onclick="backview()" value="<?php echo _("Back")?>"/>
<?php if ($exten == "vxml")  { ?>
    <input type="submit" onclick="check()" value="<?php echo _("Check")?>"/>
<?php } ?>
        <?php if ($mVxmlValidation) {
                  if ($mVxmlValidationResultCode==0)
                      $color_str = "black";
                  else
                      $color_str = "red"; ?>
        <br><br>
        <span style="font-family: Verdana; color: <?php echo $color_str; ?>;"><?php echo $mVxmlValidationResult; ?></span>
<?php } ?>
	</form>

</div>

<style type="text/css">

	.myButtons {
		width: 70px;
	}

    .CodeMirror {
    	border: 1px solid #eee;
      	margin-left: 0;
    }

    .CodeMirror-sizer {
    	margin-left: 0;
    	width: 80%;
    }
</style>

<form action="config.php?display=voximalapp&view=editor<?php echo $formurl?>" method="post" id="discard">
</form>

<script type="text/javascript">

$('#body-content').on('change keyup keydown', 'input, textarea, select', function (e) {
    $(this).addClass('changed-input');
});

$(window).on('beforeunload', function () {
    if ($('.changed-input').length) {
        return 'You haven\'t saved your changes.';
    }
});

/*
function enableBeforeUnload() {
	console.log("FUNCION enableBeforeUnload");
	window.onbeforeunload = function (e) {
		return "UNSAVED CHANGES!";
	};
}*/

function disableBeforeUnload() {
	window.onbeforeunload = null;
}

CodeMirror.commands.autocomplete = function(cm) {
	cm.showHint({hint: CodeMirror.hint.anyword});
}

function backview() {
	disableBeforeUnload();
	window.location.href=window.location.href;
}
function discard() {

	disableBeforeUnload();
	var form = document.getElementById("discard");
	form.submit();

}
function check() {

	disableBeforeUnload();
	var form = document.getElementById("applyChanges");
    form.elements['checkVxml'].value="true";
    form.elements['getfile'].value="<?php echo isset($_POST['getfile']) ? $_POST['getfile'] : $_POST['editedFile']?>";
	form.submit();

}

var editor = CodeMirror.fromTextArea(document.getElementById("code"), {

    mode: "<?php echo $mode?>",
    styleActiveLine: true,
    lineNumbers: true,
    lineWrapping: true,
    autoCloseBrackets: true,
    autoCloseTags: true,
    matchTags: {bothTags: true},
    indentUnit: 4,
    indentWithTabs: true,
    enterMode: "keep",
    tabMode: "shift",
    extraKeys: {
      "Ctrl-Space": "autocomplete"
    }
});

editor.on("update", function() {
	$(".CodeMirror-gutter.CodeMirror-linenumbers").css("width","29px");
	$(".CodeMirror-gutter-wrapper").css("left","-30px");
	$(".CodeMirror-gutter-wrapper").css("width","30px");
	$(".CodeMirror-linenumber.CodeMirror-gutter-elt").css("width","21px");
	$(".CodeMirror-linenumber.CodeMirror-gutter-elt").css("left","0px");
});

$(".CodeMirror-hscrollbar").css("left","0");
$(".CodeMirror-sizer").css("margin-left","30px");
$(".CodeMirror-gutter.CodeMirror-linenumbers").css("width","29px");
$(".CodeMirror-gutter-wrapper").css("left","-30px");
$(".CodeMirror-gutter-wrapper").css("width","30px");
$(".CodeMirror-linenumber.CodeMirror-gutter-elt").css("width","21px");
$(".CodeMirror-linenumber.CodeMirror-gutter-elt").css("left","0px");




$(document).ready(
		setTimeout(function(){
			editor.on("update",function() {
				console.log("FUNCION enableBeforeUnload");
				window.onbeforeunload = function (e) {
					return "UNSAVED CHANGES!";
				};
			});
		}, 3000)
);

//editor.setSize("100%","400px");

</script>
<?php }?>

<?php if (isset($_POST['soundfile']) && !isset($_POST['getfile'])) {?>

<script type="text/javascript">
	$(function() {
		var link = document.createElement( "link" );
		link.href = "modules/voximal/assets/soundmanager/demo/bar-ui/css/bar-ui.css";
		link.type = "text/css";
		link.rel = "stylesheet";
		link.media = "screen,print";
		document.getElementsByTagName( "head" )[0].appendChild( link );
	});
</script>
<script src="modules/voximal/assets/soundmanager/script/soundmanager2.js"></script>
<script src="modules/voximal/assets/soundmanager/demo/bar-ui/script/bar-ui.js"></script>

<span style="font-weight: bold; color: gray;"><?php echo _("Playing file")?>: </span><span><?php echo str_replace("/"," / ",str_replace($WEBDIR,"",$_POST['soundfile']))?></span><img style="width: 16px; height: 16px; margin-left: 5px; margin-bottom: 6px; cursor: pointer;" onclick="showRename()" onmouseover='' src="modules/voximal/images/edit.png">
<form action="config.php?display=voximalapp<?php echo $formurl?>" style="display: none" method="post" id="rename">
    <input type="hidden" name="view" value="editor">
	<br>
	<input type="hidden" name="oldname" value="<?php echo $_POST['soundfile']?>">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo _("Insert new name")?>: <input type="text" name="newname" value="">&nbsp;<button type="button" onclick="rename()"><?php echo _("Rename")?></button>&nbsp;<span style="color: red" id="errRename"></span>
</form>
<br><br>
<div class="sm2-bar-ui">

 <div class="bd sm2-main-controls">

  <div class="sm2-inline-texture"></div>
  <div class="sm2-inline-gradient"></div>

  <div class="sm2-inline-element sm2-button-element">
   <div class="sm2-button-bd">
    <a href="#play" class="sm2-inline-button play-pause">Play / pause</a>
   </div>
  </div>

  <div class="sm2-inline-element sm2-inline-status">

   <div class="sm2-playlist">
    <div class="sm2-playlist-target">
     <!-- playlist <ul> + <li> markup will be injected here -->
     <!-- if you want default / non-JS content, you can put that here. -->
     <noscript><p>JavaScript is required.</p></noscript>
    </div>
   </div>

   <div class="sm2-progress">
    <div class="sm2-row">
    <div class="sm2-inline-time">0:00</div>
     <div class="sm2-progress-bd">
      <div class="sm2-progress-track">
       <div class="sm2-progress-bar"></div>
       <div class="sm2-progress-ball"><div class="icon-overlay"></div></div>
      </div>
     </div>
     <div class="sm2-inline-duration">0:00</div>
    </div>
   </div>

  </div>

  <div class="sm2-inline-element sm2-button-element sm2-volume">
   <div class="sm2-button-bd">
    <span class="sm2-inline-button sm2-volume-control volume-shade"></span>
    <a href="#volume" class="sm2-inline-button sm2-volume-control">volume</a>
   </div>
  </div>

 </div>

 <div class="bd sm2-playlist-drawer sm2-element">

  <div class="sm2-inline-texture">
   <div class="sm2-box-shadow"></div>
  </div>

  <!-- playlist content is mirrored here -->

  <div class="sm2-playlist-wrapper">
    <ul class="sm2-playlist-bd">
       <li><a href="<?php echo str_replace($WEBDIR,"",$_POST['soundfile'])?>"><?php echo basename($_POST['soundfile'])?></a></li>
    </ul>
  </div>

 </div>

</div>

<style>
.sm2-bar-ui {
	font-size: 16px;
}
.sm2-bar-ui .sm2-main-controls,
.sm2-bar-ui .sm2-playlist-drawer {
	background-color: #2288cc;
}
.sm2-bar-ui .sm2-inline-texture {
	background: transparent;
}
</style>

<br><br>
<form action="config.php?display=voximalapp<?php echo $formurl?>" method="post" id="close">
    <input type="hidden" name="view" value="editor">
	<input type="submit" value="Close player">
</form>

<script>
soundManager.setup({
	url: 'modules/voximal/assets/soundmanager/swf',
 	flashVersion: 9,
 	debugMode: false,
  	onready: function() {
  		var mySound = soundManager.createSound({
  	    	id: 'playfile',
  	      	url: '<?php echo $_POST['soundfile']?>'
  	    });
  	}
});
</script>

<?php }?>



<form action="config.php?display=voximalapp<?php echo $formurl?>" method="post" id="edition">
    <input type="hidden" name="view" value="editor">
	<input type="hidden" name="getfile" value="">
</form>

<form action="config.php?display=voximalapp<?php echo $formurl?>" method="post" id="play">
    <input type="hidden" name="view" value="editor">
	<input type="hidden" name="soundfile" value="">
</form>

<script type="text/javascript">

		function send(file) {

			var form = document.getElementById("edition");
			form.elements['getfile'].value = file;
			form.submit();

		}


		$(document).ready(function() {

			$(document).on("click",".edit", function() {

				send($(this).attr("edit"));

			});

		});

		$(document).ready(function() {

			$(document).on("click",".play", function() {

				var form = document.getElementById("play");
				form.elements['soundfile'].value = $(this).attr("play");
				form.submit();

			});

		});

		function rename() {
			$("#errRename").html("");
			var form = document.getElementById("rename");
			if (form.elements['newname'].value == "" || form.elements['newname'].value == null) $("#errRename").html("You must specify a new name.");
			else {
				<?php if (isset($_POST['getfile']) && !isset($_POST['soundfile'])) {?>
				disableBeforeUnload();
				<?php }?>
				form.submit();
			}
		}

</script>
