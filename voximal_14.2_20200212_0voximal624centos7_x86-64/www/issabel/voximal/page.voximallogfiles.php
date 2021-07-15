<?php
require_once(dirname(__FILE__) .'/header.php');
$var['files'] = logfilesvox_list();
//var_dump($var['files']);

echo load_view(dirname(__FILE__) . '/views/logs.php', $var);
