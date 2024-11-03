<?php
/**
* @package phpBB-WAP
* @copyright (c) phpBB Group
* @Оптимизация под WAP: Гутник Игорь ( чел ).
* @简体中文：中文phpBB-WAP团
* @license http://opensource.org/licenses/gpl-license.php
**/

/**
* 这是一款自由软件, 您可以在 Free Software Foundation 发布的
* GNU General Public License 的条款下重新发布或修改; 您可以
* 选择目前 version 2 这个版本（亦可以选择任何更新的版本，由
* 你喜欢）作为新的牌照.
**/


if ( !defined('IN_PHPBB') )
{
	die('Hacking attempt');
}

if (!defined('E_DEPRECATED'))
{
	define('E_DEPRECATED', 8192);
}

$level = E_ALL & ~E_NOTICE & ~E_DEPRECATED;

if (version_compare(PHP_VERSION, '5.4.0-dev', '>='))
{
	if (!defined('E_STRICT'))
	{
		define('E_STRICT', 2048);
	}
	$level &= ~E_STRICT;
}

error_reporting($level);

/*
* Remove variables created by register_globals from the global scope
* Thanks to Matt Kavanagh
*/
function deregister_globals()
{
	$not_unset = array(
		'GLOBALS'	=> true,
		'_GET'		=> true,
		'_POST'		=> true,
		'_COOKIE'	=> true,
		'_REQUEST'	=> true,
		'_SERVER'	=> true,
		'_SESSION'	=> true,
		'_ENV'		=> true,
		'_FILES'	=> true
	);

	// Not only will array_merge and array_keys give a warning if
	// a parameter is not an array, array_merge will actually fail.
	// So we check if _SESSION has been initialised.
	if (!isset($_SESSION) || !is_array($_SESSION))
	{
		$_SESSION = array();
	}

	// Merge all into one extremely huge array; unset this later
	$input = array_merge(
		array_keys($_GET),
		array_keys($_POST),
		array_keys($_COOKIE),
		array_keys($_SERVER),
		array_keys($_SESSION),
		array_keys($_ENV),
		array_keys($_FILES)
	);

	foreach ($input as $varname)
	{
		if (isset($not_unset[$varname]))
		{
			// Hacking attempt. No point in continuing unless it's a COOKIE (so a cookie called GLOBALS doesn't lock users out completely)
			if ($varname !== 'GLOBALS' || isset($_GET['GLOBALS']) || isset($_POST['GLOBALS']) || isset($_SERVER['GLOBALS']) || isset($_SESSION['GLOBALS']) || isset($_ENV['GLOBALS']) || isset($_FILES['GLOBALS']))
			{
				exit;
			}
			else
			{
				$cookie = &$_COOKIE;
				while (isset($cookie['GLOBALS']))
				{
					if (!is_array($cookie['GLOBALS']))
					{
						break;
					}

					foreach ($cookie['GLOBALS'] as $registered_var => $value)
					{
						if (!isset($not_unset[$registered_var]))
						{
							unset($GLOBALS[$registered_var]);
						}
					}
					$cookie = &$cookie['GLOBALS'];
				}
			}
		}

		unset($GLOBALS[$varname]);
	}

	unset($input);
}

if (function_exists('date_default_timezone_set') && function_exists('date_default_timezone_get'))
{
	date_default_timezone_set(@date_default_timezone_get());
}

// 魔术引号在 PHP 5.3.0 起废弃并将自 PHP 5.4.0 起移除。
if (version_compare(PHP_VERSION, '5.4.0-dev', '>='))
{
	define('MAGIC_QUOTES', false);
}
else
{
	// 尝试关闭魔术引号
	@set_magic_quotes_runtime(0);
	
	// Be paranoid with passed vars
	if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on' || !function_exists('ini_get'))
	{
		deregister_globals();
	}

	define('MAGIC_QUOTES', (get_magic_quotes_gpc()) ? true : false);
}

// 页面的执行时间类
require(ROOT_PATH . 'includes/functions/runtime.php');

$starttime = start_runtime();

$board_config 		= array();
$userdata 			= array();
$nav_links 			= array();
$images 			= array();
$gen_simple_header 	= FALSE;

@include(ROOT_PATH . 'config.php');

// 转到安装
if( !defined('PHPBB_INSTALLED') )
{
	header('Location: ' . ROOT_PATH . 'install/install.php');
	exit;
}

//常量
require(ROOT_PATH . 'includes/constants.php');

//模版解析
require(ROOT_PATH . 'includes/class/template.php');

//session
require(ROOT_PATH . 'includes/class/session.php');

//权限
require(ROOT_PATH . 'includes/functions/auth.php');

//常用函数
require(ROOT_PATH . 'includes/functions/common.php');

// 自定义的错误处理
set_error_handler('error_message');

//数据库
require(ROOT_PATH . 'includes/class/mysql.php');

$db = new sql_db($dbhost, $dbuser, $dbpasswd, $dbname, false);

if(!$db->db_connect_id)
{
	die('<!DOCTYPE HTML><html><head><meta http-equiv="content-type" content="text/html; charset=utf-8" /><title>提示</title><style type="text/css">@charset "utf-8";*{margin:0;padding:0;}body{margin:0 auto;max-width:640px;font-family:"Century Gothic","Microsoft yahei";background-color:#F9F9F9;}#wrap{background-color:#FFF;width:640px;}.error{padding:20px;margin:0;border-style:solid;border-width:1px;border-color:#000;}.main{padding:115px 0 6px 0;}</style></head><body><div id="wrap"><div class="main"><div class="error"><p style="color:red;">无法链接到数据库，请检查您的数据库配置文件是否正确</p></div></div><div></body></html>');
}

$db->sql_query('SET NAMES utf8mb4');

// 为了安全起见，注销数据库密码这个变量
unset($dbpasswd);

// 引入缓存功能
// 虽然目前的缓存系统比较弱
require(ROOT_PATH . 'includes/class/cache.php');
$cache = new cache();
$board_config = $cache->export('global_config');

$session = new Session($db);

$http_user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : getenv('HTTP_USER_AGENT');

if (strpos($http_user_agent, 'Edg') !== false) {
    $user_agent = 'Microsoft Edge';
} elseif (strpos($http_user_agent, 'MSIE') !== false || strpos($http_user_agent, 'Trident') !== false) {
    $user_agent = 'Internet Explorer';
} elseif (strpos($http_user_agent, 'Firefox') !== false) {
    $user_agent = 'Firefox';
} elseif (strpos($http_user_agent, 'Chrome') !== false && strpos($http_user_agent, 'Safari') !== false && strpos($http_user_agent, 'Edg') === false) {
    $user_agent = 'Chrome';
} elseif (strpos($http_user_agent, 'Safari') !== false && strpos($http_user_agent, 'Chrome') === false && strpos($http_user_agent, 'Edg') === false) {
    $user_agent = 'Safari';
} elseif (strpos($http_user_agent, 'Opera') !== false || strpos($http_user_agent, 'OPR') !== false) {
    $user_agent = 'Opera';
} elseif (strpos($http_user_agent, 'SamsungBrowser') !== false) {
    $user_agent = 'Samsung Internet';
} elseif (strpos($http_user_agent, 'YaBrowser') !== false) {
    $user_agent = 'Yandex Browser';
} elseif (strpos($http_user_agent, 'Vivaldi') !== false) {
    $user_agent = 'Vivaldi';
} elseif (strpos($http_user_agent, 'UC Browser') !== false) {
    $user_agent = 'UC Browser';
} elseif (strpos($http_user_agent, 'QQBrowser') !== false) {
    $user_agent = 'QQ Browser';
} else {
    $user_agent = strtok($http_user_agent, '/');
}
//获取cdn真实的ip
$client_ip = '';
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $client_ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $client_ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
} elseif (!empty($_SERVER['REMOTE_ADDR'])) {
    $client_ip = $_SERVER['REMOTE_ADDR'];
} else {
    $client_ip = getenv('REMOTE_ADDR');
}
$client_ip = filter_var($client_ip, FILTER_VALIDATE_IP) ?: '无效的 IP 地址';
$user_ip 		= encode_ip($client_ip, false);

if( $board_config['board_disable'] && !defined("IN_ADMIN") && !defined("IN_LOGIN") )
{
	// 随你喜欢
	// trigger_error('对不起, 该论坛暂时不可用, 请稍候重试', E_USER_ERROR);
	trigger_error('对不起, 该论坛暂时不可用, 请稍候重试', E_USER_WARNING);
}
?>