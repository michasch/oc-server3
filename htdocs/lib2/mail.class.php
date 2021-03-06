<?php
/***************************************************************************
 *  For license information see doc/license.txt
 *
 *  Unicode Reminder メモ
 ***************************************************************************/

require_once($opt['rootpath'] . 'lib2/smarty/Smarty.class.php');

class mail extends Smarty
{
	var $name = 'sys_nothing';
	var $main_template = 'sys_main';
	var $compile_id = null;

	var $from = '';
	var $to = '';
	var $subject = '';

	var $replyTo = null;
	var $returnPath = null;

	var $headers = array();

	function mail()
	{
		global $opt;

		$this->template_dir = $opt['rootpath'] . 'templates2/mail/';
		$this->compile_dir = $opt['rootpath'] . 'cache2/smarty/compiled/';
		$this->plugins_dir = array('plugins', 'ocplugins');

		// disable caching ...
		$this->caching = false;

		// register additional functions
		$this->load_filter('pre', 't');

		// cache control
		if (($opt['debug'] & DEBUG_TEMPLATES) == DEBUG_TEMPLATES)
			$this->force_compile = true;

		$this->from = $opt['mail']['from'];
	}

	function get_compile_id()
	{
		global $opt;
		return 'mail|' . $opt['template']['locale'] . '|' . $this->compile_id;
	}

	function assign_rs($name, $rs)
	{
		$items = array();
		while ($r = sql_fetch_assoc($rs))
			$items[] = $r;
		$this->assign($name, $items);
	}

	function send()
	{
		global $tpl, $opt, $login;

		if (!$this->template_exists($this->name . '.tpl'))
			$tpl->error(ERROR_MAIL_TEMPLATE_NOT_FOUND);
		$this->assign('template', $this->name);

		$optn['mail']['contact'] = $opt['mail']['contact'];
		$optn['page']['absolute_url'] = $opt['page']['absolute_url'];
		$optn['format'] = $opt['locale'][$opt['template']['locale']]['format'];
		$this->assign('opt', $optn);

		$this->assign('to', $this->to);
		$this->assign('from', $this->from);
		$this->assign('subject', $this->subject);

		$llogin['username'] = isset($login) ? $login->username : '';
		$this->assign('login', $llogin);

		$body = $this->fetch($this->main_template . '.tpl', '', $this->get_compile_id());

		// check if the target domain exists if the domain does not
		// exist, the mail is sent to the own domain (?!)
		$domain = mail::getToMailDomain($this->to);
		if (mail::is_existent_maildomain($domain) == false)
			return false;

		$aAddHeaders = array();
		$aAddHeaders[] = 'From: "' . $this->from . '" <' . $this->from . '>';

		if ($this->replyTo !== null)
			$aAddHeaders[] = 'Reply-To: ' . $this->replyTo;

		if ($this->returnPath !== null)
			$aAddHeaders[] = 'Return-Path: ' . $this->returnPath;

		$mailheaders = implode("\n", array_merge($aAddHeaders, $this->headers));
		return mb_send_mail($this->to, $opt['mail']['subject'] . $this->subject, $body, $mailheaders);
	}

	static function is_existent_maildomain($domain)
	{
		$smtp_serverlist = array();
		$smtp_serverweight = array();

		if (getmxrr($domain, $smtp_serverlist, $smtp_serverweight) != false)
			if (count($smtp_serverlist)>0)
				return true;

		// check if A exists
		$a = dns_get_record($domain, DNS_A);
		if (count($a) > 0)
			return true;

		return false;
	}

	static function getToMailDomain($mail)
	{
		if ($mail == '')
			return '';

		if (strrpos($mail, '@') === false)
			$domain = 'localhost';
		else
			$domain = substr($mail, strrpos($mail, '@') + 1);

		return $domain;
	}
}
?>