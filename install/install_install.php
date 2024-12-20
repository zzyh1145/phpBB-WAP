<?php 

function phpbb_install_wellcome()
{
echo <<<HTML
<!DOCTYPE html PUBLIC " -//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
		<meta name="viewport" content="width=device-width; initial-scale=1.0; minimum-scale=1.0; maximum-scale=2.0"/>
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black" />			
		<title>欢迎使用_phpBB-WAP</title>
		<link rel="shortcut icon" href="../favicon.ico" />
		<link rel="stylesheet" href="style.css" type="text/css" />	
	</head>
	<body>
		<div class="wrap">
			<header><a href="http://phpbb-wap.com"><img src="../images/logo.png" /></a></header>
			<div class="title">欢迎使用</div>
			<form action="install.php?install=check" method="post">
				<div class="license">
					<h1>介绍</h1>
					<p>phpBB-WAP 中文版取自于 phpbb-wap.ru 中的 phpBB-WAP v8 版本，而 phpBB-WAP 是世界上知名的 phpBB 论坛开源软件的移动终端版本。phpBB-WAP 这个名字，是 PHP Bulletin Board Wireless Application Protocol 的缩写。</p>
					<p>简体中文制作: <a href="http://phpbb-wap.com/">phpBB-WAP Group</a></p>
					<p>Модификация: <a href="http://phpbb-wap.ru/">Гутник Игорь ( чел )</a></p>
					<p>Copyright: <a href="http://phpbb.com/">phpBB Group</a></p>
					<h1>License</h1>
					<p>This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.</p>
					<p>这是一款自由软件, 您可以在 Free Software Foundation 发布的 GNU General Public License 的条款下重新发布或修改; 您可以 选择目前 version 2 这个版本（亦可以选择任何更新的版本，由 你喜欢）作为新的牌照.</p>
					<p>欲想了解更多，请点击 <a href="http://opensource.org/licenses/GPL-2.0">General Public License</a></p>
					<h1>问题反馈</h1>
					<p>如果您在安装的过程中遇到问题或者您对phpBB-WAP有什么好的意见和想法可以访问下面链接进行交流反馈：</p>
					<p><a href="http://wapz.me/viewforum.php?f=15">http://wapz.me/viewforum.php?f=15</a></p>
				</div>
				<div class="button-box">
					<input type="submit" class="button" value="下一步" />
				</div>
			</form>
			<footer>Powered by <a href="http://phpbb-wap.com/">phpBB-WAP</a> Group</footer>
		</div>
	</body>
</html>
HTML;
}

function can_load_dll($dll)
{
	// SQLite2 is a tricky thing, from 5.0.0 it requires PDO; if PDO is not loaded we must state that SQLite is unavailable
	// as the installer doesn't understand that the extension has a prerequisite.
	//
	// On top of this sometimes the SQLite extension is compiled for a different version of PDO
	// by some Linux distributions which causes phpBB to bomb out with a blank page.
	//
	// Net result we'll disable automatic inclusion of SQLite support
	//
	// See: r9618 and #56105
	if ($dll == 'sqlite')
	{
		return false;
	}
	return ((@ini_get('enable_dl') || strtolower(@ini_get('enable_dl')) == 'on') && (!@ini_get('safe_mode') || strtolower(@ini_get('safe_mode')) == 'off') && function_exists('dl') && @dl($dll . '.' . PHP_SHLIB_SUFFIX)) ? true : false;
}

function phpbb_install_check()
{

	$check = true; 

	if (version_compare(PHP_VERSION, '5.2', '>='))
	{
		$phpversion = '<span class="green">√ ' . PHP_VERSION . '</span>';
	}
	else
	{
		$phpversion = '<span class="red">×</span>';
		$check = false;
	}

	if (function_exists('curl_init'))
	{
		$curl = '<span class="green">√</span>';
	}
	else
	{
		$curl = '<span class="red">×</span>';
		$check = false;
	}

	if (@ini_get('allow_url_fopen') == '1' || strtolower(@ini_get('allow_url_fopen')) == 'on')
	{
		$allow_url_fopen = '<span class="green">√</span>';
	}
	else
	{
		$allow_url_fopen = '<span class="red">×</span>';
		$check = false;
	}

	if (function_exists('getimagesize'))
	{
		$getimagesize = '<span class="green">√</span>';
	}
	else
	{
		$getimagesize = '<span class="red">×</span>';
		$check = false;
	}

	if (function_exists('mysqli_connect'))
	{
		$mysql = '<span class="green">√</span>';
	}
	else
	{
		$mysql = '<span class="red">×</span>';
		$check = false;
	}

	if (version_compare(PHP_VERSION, '5.4.0-dev') < 0)
	{
		if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on')
		{
			$register_globals = '<span class="green">x</span>';
			$check = false;
		}
		else
		{
			$register_globals = '<span class="green">√</span>';
		}
	}

	if (@extension_loaded('gd') || can_load_dll('gd'))
	{
		$gd = '<span class="green">√</span>';
	}
	else
	{
		$gd = '<span class="green">x</span>';
		$check = false;
	}

	if (@preg_match('//u', ''))
	{
		$preg_match = '<span class="green">√</span>';
	}
	else
	{
		$preg_match = '<span class="green">x</span>';
		$check = false;
	}

	$directories = 'cache/';

	umask(0);

	$exists = $write = false;

	if (!file_exists(ROOT_PATH . $directories))
	{
		@mkdir(ROOT_PATH . $directories, 0777);
		phpbb_chmod(ROOT_PATH . $directories, CHMOD_READ | CHMOD_WRITE);
	}

	if (file_exists(ROOT_PATH . $directories) && is_dir(ROOT_PATH . $directories))
	{
		phpbb_chmod(ROOT_PATH . $directories, CHMOD_READ | CHMOD_WRITE);
		$exists = true;
	}

	$fp = @fopen(ROOT_PATH . $directories . 'test_lock', 'wb');

	if ($fp !== false)
	{
		$write = true;
	}

	@fclose($fp);

	@unlink(ROOT_PATH . $directories . 'test_lock');

	if ($exists && $write)
	{
		$cache = '<span class="green">√</span>';
	}
	else
	{
		$cache = '<span class="red">x</span>';
		$check = false;
	}

	$config = '<span class="green">√</span>';
	$write = $exists = true;
	if (file_exists(ROOT_PATH . 'config.php'))
	{
		if (!phpbb_is_writable(ROOT_PATH . 'config.php'))
		{
			$write = false;
			$check = false;
			$config = '<span class="red">x</span>';
		}
	}
	else
	{
		$write = $exists = false;
		$check = false;
		$config = '<span class="red">x</span>';
	}

	if ($check)
	{
		$message = '<span class="green">您的服务器兼容此程序，继续吗？</span>';
		$install_submit = '<span class="green">全新安装</span>';
		$update_submit = '<span class="green">升级程序</span>';
	}
	else
	{
		$message = '<span class="red">您的服务器对该版本不能完全支持，强制安装/升级会使某些功能不能正常使用，继续吗？</span>';

		$install_submit = '<span class="red">强制安装</span>';
		$update_submit = '<span class="red">强制升级</span>';
	}




echo <<<HTML
<!DOCTYPE html PUBLIC " -//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
		<meta name="viewport" content="width=device-width; initial-scale=1.0; minimum-scale=1.0; maximum-scale=2.0"/>
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black" />			
		<title>服务器兼容性检测_phpBB-WAP</title>
		<link rel="shortcut icon" href="../favicon.ico" />
		<link rel="stylesheet" href="style.css" type="text/css" />	
	</head>
	<body>
		<div class="wrap">
			<header><a href="http://phpbb-wap.com"><img src="../images/logo.png" /></a></header>
			<div class="title">服务器兼容性检测</div>
			<form action="install.php" method="get">
				<div class="license">
					<h2>
						<div class="left l_title">PHP 版本 >= 5.2</div>
						<div class="left r_title">{$phpversion}</div>
						<div class="clear"></div>
					</h2>
					<p><strong>必须</strong>，要安装phpBB-WAP，您必需正在运行最低 5.2 版本的PHP</p>
					<h2>
						<div class="left l_title">PCRE UTF-8 支持</div>
						<div class="left r_title">{$preg_match}</div>
						<div class="clear"></div>
					</h2>
					<p><strong>必须</strong>，否则Module BBCode无法正常工作</p>
					<h2>
						<div class="left l_title">CURL</div>
						<div class="left r_title">{$curl}</div>
						<div class="clear"></div>
					</h2>
					<p><strong>必须</strong></p>
					<h2>
						<div class="left l_title">启用 allow_url_fopen</div>
						<div class="left r_title">{$allow_url_fopen}</div>
						<div class="clear"></div>
					</h2>
					<p><strong>必须</strong></p>
					<h2>
						<div class="left l_title">getimagesize() 函数可用</div>
						<div class="left r_title">{$getimagesize}</div>
						<div class="clear"></div>
					</h2>
					<p><strong>必须</strong>，否则相册，附件，表情，头像的部分功能将不可用</p>
					<h2>
						<div class="left l_title">关闭全局变量</div>
						<div class="left r_title">{$register_globals}</div>
						<div class="clear"></div>
					</h2>
					<p><strong>可选</strong>，如果此设置被允许，phpBB-WAP仍然会运行。但出于安全考虑，如果条件允许，建议您将 register_globals 禁用。</p>
					<h2>
						<div class="left l_title">GD库扩展</div>
						<div class="left r_title">{$gd}</div>
						<div class="clear"></div>
					</h2>
					<p><strong>可选</strong>，用于创建登录、发帖验证图片</p>
					<h2>
						<div class="left l_title">cache 目录读写权限</div>
						<div class="left r_title">{$cache}</div>
						<div class="clear"></div>
					</h2>
					<p><strong>必须</strong>，用于缓存网站数据</p>
					<h2>
						<div class="left l_title">config.php 读写权限</div>
						<div class="left r_title">{$config}</div>
						<div class="clear"></div>
					</h2>
					<p><strong>必须</strong></p>				
					<h2>
						<div class="left l_title">MySQL 数据库支持</div>
						<div class="left r_title">{$mysql}</div>
						<div class="clear"></div>
					</h2>
					<p><strong>必须</strong>，且Mysql 版本必需 >= 5.0</p>
				</div>
				<div class="row">{$message}</div>
				<div class="row">
					<div class="left l_title">
						<div class="center">
							<input type="radio" name="install" value="install" checked="checked" /> <strong>{$install_submit}</strong>
						</div>
					</div>
					<div class="left">
						<div class="center">
							<input type="radio" name="install" value="update" /> <strong>{$update_submit}</strong>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<div class="button-box">
					<input type="submit" class="button" value="继续"/>
				</div>
			</form>
			<footer>Powered by <a href="http://phpbb-wap.com/">phpBB-WAP</a> Group</footer>
		</div>
	</body>
</html>
HTML;
}

function phpbb_install_header($text, $form_action = false)//page_header($text, $form_action = false)
{
	global $install_step;

	$install_step_text = ( $install_step == 1 ) ? '填写信息 &gt; <font color="red">完成安装</font>' : '<font color="red">填写信息</font> &gt; 完成安装';
	
	$s_action = ($form_action) ? $form_action : 'install.php';

echo <<<HTML
<!DOCTYPE html PUBLIC " -//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
		<meta name="viewport" content="width=device-width; initial-scale=1.0; minimum-scale=1.0; maximum-scale=2.0"/>
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black" />			
		<title>phpBB-WAP_安装向导</title>
		<link rel="shortcut icon" href="../favicon.ico" />
		<link rel="stylesheet" href="style.css" type="text/css" />	
	</head>
	<body>
		<div class="wrap">
			<header><a href="http://phpbb-wap.com"><img src="../images/logo.png" /></a></header>
			<div class="row">{$text}</div>
			<div class="row1">
				<form action="{$s_action}" name="install" method="post">
HTML;
}

function phpbb_install_footer()//page_footer()
{
echo <<<HTML
				</form>
			</div>
			<footer>Powered by <a href="http://phpbb-wap.com/">phpBB-WAP</a> Group</footer>
		</div>
	</body>
</html>
HTML;
}
	
function phpbb_install_common_form($hidden, $submit)//page_common_form($hidden, $submit)
{
echo <<<HTML
		{$hidden}
		<div class="button-box">
			<input type="submit" value="{$submit}" />
		</div>
HTML;
}

function phpbb_install_error_header($error_title)//page_error_header($error_title)
{
echo <<<HTML
<!DOCTYPE html PUBLIC " -//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
	<meta name="viewport" content="width=device-width; initial-scale=1.0; minimum-scale=1.0; maximum-scale=2.0"/>
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />			
	<title>{$error_title}</title>
	<link rel="shortcut icon" href="../favicon.ico" />
	<link rel="stylesheet" href="style.css" type="text/css" />	
</head>
<body>
	<div class="wrap">
		<header><a href="http://phpbb-wap.com"><img src="../images/logo.png" /></a></header>
HTML;
}
	
function phpbb_install_error($error_title, $error)//page_error($error_title, $error)
{
echo <<<HTML
		<div class="title">{$error_title}</div>
		<div class="error">
			<div class="center">
				{$error}
			</div>
		</div>
HTML;
}

function phpbb_install_title($name)
{
echo <<<HTML
		<div class="title">
			<div class="center">{$name}</div>
		</div>
HTML;
}

function phpbb_install_tbody($left, $right)
{
echo <<<HTML
		<div class="row">
			<div class="left l_title">&nbsp;{$left}：</div>
			<div class="left">{$right}&nbsp;</div>
			<div class="clear"></div>
		</div>
HTML;
}

function phpbb_update_error($error)
{
echo <<<HTML
<!DOCTYPE html PUBLIC " -//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
		<meta name="viewport" content="width=device-width; initial-scale=1.0; minimum-scale=1.0; maximum-scale=2.0"/>
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black" />			
		<title>phpBB-WAP_升级失败</title>
		<link rel="shortcut icon" href="../favicon.ico" />
		<link rel="stylesheet" href="style.css" type="text/css" />	
	</head>
	<body>
		<div class="wrap">
			<header><a href="http://phpbb-wap.com"><img src="../images/logo.png" /></a></header>
			<div class="title">升级失败</div>
			<div class="error">{$error}</div>
			<div class="footer">
				<div class="copy">
					Powered by <a href="http://phpbb-wap.com/">phpBB-WAP</a> Group
				</div>
			</div>
		</div>
	</body>
</html>
HTML;
}	

function phpbb_update_complete($update_error, $sql_list)
{
	global $db;

if ($update_error)
{
	$title = '升级失败';
	$message = '<p>很遗憾，没有帮您成功升级，因为有一些SQL语句没有成功执行：</p><hr />';
	$message .= $sql_list;
	$message .= '<p>您可以将上面的SQL使用电子邮件发送到 bug@phpbb-wap.com 进行报告</p>';
}
else
{
	$title = '升级完成';
	$message = '当您看到这个界面时说明程序已经升级完成，记得把install文件夹删除哦！';
}

echo <<<HTML
<!DOCTYPE html PUBLIC " -//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
		<meta name="viewport" content="width=device-width; initial-scale=1.0; minimum-scale=1.0; maximum-scale=2.0"/>
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black" />			
		<title>phpBB-WAP_{$title}</title>
		<link rel="shortcut icon" href="../favicon.ico" />
		<link rel="stylesheet" href="style.css" type="text/css" />	
	</head>
	<body>
		<div class="wrap">
			<header><a href="http://phpbb-wap.com"><img src="../images/logo.png" /></a></header>
			<div class="title">{$title}</div>
			<div class="row">{$message}</div>
			<footer>Powered by <a href="http://phpbb-wap.com/">phpBB-WAP</a> Group</footer>
		</div>
	</body>
</html>	
HTML;
	
	if (!empty($db))
	{
		$db->sql_close();
	}

	if (file_exists(ROOT_PATH . 'cache/global_config.php'))
	{
		unlink(ROOT_PATH . 'cache/global_config.php');
	}

	exit;
}

function phpbb_update_wellcome()
{

$message = '';
$update = '';
if ( !defined('PHPBB_INSTALLED') )
{
	$update = '请先恢复您的 config.php 文件';
	$message .= '<div class="error"><span class="red">注意：升级程序版本必需保存原版本的 config.php 文件里面的内容，请检查目前 config.php 中是否还有数据库的信息？</span></div>';
}
else
{
	$update = '<input type="hidden" name="update" value="1" /><input type="submit" class="button" name="updating" value="确认升级"/>';
}

echo <<<HTML
<!DOCTYPE html PUBLIC " -//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
		<meta name="viewport" content="width=device-width; initial-scale=1.0; minimum-scale=1.0; maximum-scale=2.0"/>
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black" />			
		<title>升级程序</title>
		<link rel="shortcut icon" href="../favicon.ico" />
		<link rel="stylesheet" href="style.css" type="text/css" />	
	</head>
	<body>
		<div class="wrap">
			<header><a href="http://phpbb-wap.com"><img src="../images/logo.png" /></a></header>
			<div class="title">欢迎升级您的程序</div>
			{$message}
			<form name="install" action="install.php?install=update" method="post">
				<div class="row">
					<p>升级有风险，请务必备份您的网站数据</p>
				</div>
				<div class="button-box">
					{$update}
				</div>
			</form>
			<footer>Powered by <a href="http://phpbb-wap.com/">phpBB-WAP</a> Group</footer>
		</div>
	</body>
</html>
HTML;
}

?>