#!/usr/local/bin/php -q
<?php
/***************************************************************************
 *	For license information see doc/license.txt
 *
		Unicode Reminder メモ
 ***************************************************************************/

	// needs absolute rootpath because called as cronjob
	$rootpath = dirname(__FILE__) . '/../../';

	// chdir to proper directory (needed for cronjobs)
	chdir(substr(realpath($_SERVER['PHP_SELF']), 0, strrpos(realpath($_SERVER['PHP_SELF']), '/')));

  require_once($rootpath . 'lib/settings.inc.php');
  require_once($rootpath . 'lib/clicompatbase.inc.php');

/* begin db connect */
	db_connect();
	if ($dblink === false)
	{
		echo 'Unable to connect to database';
		exit;
	}
/* end db connect */

	$rs = sql('SELECT `cache_id` FROM `caches` WHERE ISNULL(`wp_oc`)');
	while ($r = sql_fetch_array($rs))
	{
		setCacheWaypoint($r['cache_id']);
	}
	mysql_free_result($rs);
?>
