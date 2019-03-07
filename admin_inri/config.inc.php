<?php

$CFG = array(
#db_config
	"db_hostname" => "localhost",
	"db_username" => "inri",
	"db_password" => "eEKBcXPr75CZcJEF",
	"db_basename" => "inri" 
#end_db_config
);

$_SESSION["DB_OPENED"] = FALSE;
$_SESSION["NEX_CFG"] =& $CFG;
