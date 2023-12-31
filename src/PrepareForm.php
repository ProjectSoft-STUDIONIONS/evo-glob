<?php

namespace ProjectSoft;

class PrepareForm {

	public static function prepareProcessCallme($modx, $data, $fl, $name)
	{
		$cfg = $fl->config->getConfig();
		$site = $modx->config['site_name'];
		$theme = $fl->getField("formid");
		switch($theme){
			default:
				$theme_val = "Заказ звонка";
				break;
		}
		$fl->mailConfig['subject']  = $cfg["subject"] = mb_strtoupper($theme_val, $modx->config['modx_charset']) . " с сайта «" . $site . "»";
		$fl->mailConfig['replyTo']  = $cfg["replyTo"] = $modx->config['email_bot'];
		$fl->mailConfig['fromName']  = $cfg["fromName"] = $modx->config['email_bot_name'];
		$fl->config->setConfig($cfg);
	}

	public static function prepareCallme($modx, $data, $fl, $name)
	{
		$id = $modx->documentIdentifier;
		$url = $modx->makeUrl($id, '', '');
		$port = ((isset ($_SERVER['HTTPS']) && ( (strtolower($_SERVER['HTTPS']) == 'on') || ($_SERVER['HTTPS']) == '1')) || $_SERVER['SERVER_PORT'] == 443 || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
		$input = $_SERVER['HTTP_HOST'];
		$idna = new idna_convert();
		$host = $port . $idna->decode($input) . $url;
		/**
		$theme = $fl->getField("formid");
		$theme_val = "Заказ звонка";
		$fl->setField("theme", $theme_val);
		**/
		$fl->setField("pagetitle", $modx->documentObject["pagetitle"]);
		$fl->setField("url", $host);
	}
	
	/**
	 * onSendBot Event
	**/
	public static function prepareAfterProcessQuestion($modx, $data, $fl, $name)
	{
		$theme = $fl->getField("formid");
		$theme_val = "Вопрос с сайта " . $modx->config['site_name'];
		$message = $fl->getField('message');
		$message = $message ? $message : '';
		$re = '/^(.*\:|(?:.*))(.*)/m';
		$subst = '*$1* $2';
		$message = preg_replace($re, $subst, $message);
		$page = '' . $modx->documentObject["pagetitle"] . " _" . $fl->getField('url') . "_";
		$msg_str = 'Страница отправки';
		$arr = array(
			"types" => array(
				'date'		=> 'Дата',
				'theme'		=> 'Тема',
				'name'		=> 'Имя',
				'email'		=> 'Email',
				'phone'		=> 'Телефон',
				'message'	=> 'Сообщение',
				'url'		=> 'Страница отправки'
			),
			"fields" => array(
				'date'		=> date('d.m.Y H:i:s', time() + $modx->config['server_offset_time']),
				'theme'		=> $theme_val,
				'name'		=> $fl->getField('first_name'),
				'email'		=> $fl->getField('email'),
				'phone'		=> $fl->getField('phone'),
				'message'	=> $message,
				'url'		=> $page
			),
			"parse_mode" => "Markdown",
		);
		$modx->invokeEvent('ОnSendBot', $arr);
	}
}