<?php

$li[] = '<a href="config.php?display=voximal">' . _("Add New Application") . '</a><hr>';

if (!empty($names)) {
	foreach ($names as $name) {
		$li[] = '<a href="config.php?display=voximal&action=edit&vxml='.$name['name'].'">'.$name['name'].'</a>';
	}
}

echo '<div class="rnav">' . ul($li) . '</div>';
?>