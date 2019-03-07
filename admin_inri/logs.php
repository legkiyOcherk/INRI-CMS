<?php
if (!defined("WA_PATH")) define("WA_PATH", "./");
if (!defined("NX_PATH")) define("NX_PATH", "../");
if (!defined("IA_URL")) define("IA_URL", "/iladmin/");
require_once(WA_PATH.'lib/global.lib.php');
require_once(WA_PATH.'config.inc.php');
require_once(WA_PATH.'lib/mysql.lib.php');
require_once(WA_PATH.'auth.inc.php');
if (!(isset($_SESSION["WA_USER"]) && $_SESSION["WA_USER"]["is_admin"])) {include(WA_PATH.'index.php'); die();include(WA_PATH.'footer.inc.php');}
	db_open();
	
	include(WA_PATH.'header.inc.php');
	echo "<table class='table'><tr><th>дата</th><th>Пользователь</th><th>действия</th><th>изменения</th><th>скрипт</th></tr>";
	$q=mysql_query("
    SELECT *, DATE_FORMAT(`il_admin_logs`.`date_time`, '%d.%e.%y %T'), il_accounts.login as login  
    FROM `il_admin_logs` 
    LEFT JOIN `il_accounts` on il_accounts.id=il_admin_logs.user_id 
    ORDER BY  `date_time` DESC
  ") or die(mysql_error());
	while ($act=mysql_fetch_assoc($q))
	{	
		extract ($act);
		echo "<tr><td>$date_time</td><td>$login</td><td></td><td>$changes</td><td>$script</td></tr>";
	}
	echo "</table>";
include(WA_PATH.'footer.inc.php');
?>