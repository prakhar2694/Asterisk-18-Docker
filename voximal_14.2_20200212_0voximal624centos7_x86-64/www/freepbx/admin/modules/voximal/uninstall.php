<?php

if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
sql('DROP TABLE IF EXISTS voximal');
sql('DROP TABLE IF EXISTS voximallicense');
sql('DROP TABLE IF EXISTS voximalconfiguration'); // Old configuration table.
sql('DROP TABLE IF EXISTS voximalkey');

//Deprecated:
sql('DROP TABLE IF EXISTS voximaltts');
sql('DROP TABLE IF EXISTS voximalasr');
