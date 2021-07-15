<?php
//Check if user is "logged in"
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }


$viewAccounts      = true;
$viewAccountsClass = 'class="active"';
$viewEditorClass   = '';
if (isset($_REQUEST['view']) && $_REQUEST['view'] == "editor"){
    $viewAccounts = false;
    $viewEditorClass = 'class="active"';
    $viewAccountsClass = '';
}


?>


<style TYPE = "text/css">
#current {
	text-decoration: underline;
}
ul#tabnav {
    font: bold;
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

<?php if (!isset($_REQUEST["fw_popover"])) { ?>
<h2><?php echo _("Voximal Application")?></h2>
<table width="700px">
		<tr>
			<td>
                <ul id="tabnav">
                     <li <?php echo $viewAccountsClass;?>><a href="config.php?display=voximalapp&view=accounts"><?php echo _("Accounts")?></a></li>
                     <li <?php echo $viewEditorClass;?>><a href="config.php?display=voximalapp&view=editor"><?php echo _("Editor")?></a></li>
                </ul>
             </td>
		</tr>
</table>
<?php
    }
	if (!$viewAccounts) {
		echo load_view(dirname(__FILE__) . '/views/editor.voximalapp.php');
	} else {
		echo load_view(dirname(__FILE__) . '/views/accounts.voximalapp.php');
	}
?>

