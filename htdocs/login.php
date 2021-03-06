<?php
/***************************************************************************
 *  For license information see doc/license.txt
 *
 *  Unicode Reminder メモ
 *
 *  Display some status information about the server and Opencaching
 ***************************************************************************/

	require('./lib2/web.inc.php');
	$tpl->name = 'login';
	$tpl->menuitem = MNU_LOGIN;

	$login->verify();

	$tpl->assign('error', LOGIN_OK);

	$target = isset($_REQUEST['target']) ? $_REQUEST['target'] : 'myhome.php';
	if (mb_strtolower(mb_substr($target, 0, 9)) == 'login.php')
		$target = 'myhome.php';

	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

	if ($action == 'cookieverify')
	{
		// we should be logged in ... check if cookie is set ...
		if ($opt['session']['mode'] == SAVE_SESSION)
		{
			if (!isset($_REQUEST['SESSION']))
				$tpl->error(ERROR_NO_COOKIES);
			else
				$tpl->redirect($target);
		}
		else
		{
			if (!isset($_COOKIE[$opt['session']['cookiename'] . 'data']))
				$tpl->error(ERROR_NO_COOKIES);
			else
				$tpl->redirect($target);
		}
	}
	else if ($action == 'logout')
	{
		$login->logout();
		$tpl->assign('error', LOGIN_LOGOUT_OK);
	}
	else
	{
		if ($login->userid != 0)
			$tpl->error(ERROR_ALREADY_LOGGEDIN);

		$username = isset($_POST['email']) ? $_POST['email'] : '';
		$password = isset($_POST['password']) ? $_POST['password'] : '';

		$retval = $login->try_login($username, $password, null);
		$password = '';
		if ($retval == LOGIN_OK)
			$tpl->redirect('login.php?action=cookieverify&target=' . urlencode($target));

		$tpl->assign('username', $username);
		$tpl->assign('error', $retval);
	}
	$tpl->assign('target', $target);

	$tpl->display();
?>