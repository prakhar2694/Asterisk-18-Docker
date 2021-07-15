<?php

//IssabelPBX special link for assests : needed for embeded
//https://github.com/IssabelFoundation/issabelPBX/blob/master/build/issabelpbx.spec
unlink('/var/www/html/modules/voximal/assets');
unlink('/var/www/html/modules/voximal/images');
//Ignore if rep is not empty ...maybe voximal module exists
@rmdir('/var/www/html/modules/voximal');


if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
sql('DROP TABLE IF EXISTS voximal');
sql('DROP TABLE IF EXISTS voximallicense');
sql('DROP TABLE IF EXISTS voximalconfiguration'); // Old configuration table.
sql('DROP TABLE IF EXISTS voximalkey');

//Deprecated:
sql('DROP TABLE IF EXISTS voximaltts');
sql('DROP TABLE IF EXISTS voximalasr');
