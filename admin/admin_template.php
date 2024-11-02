<?php
/**
* @package phpBB-WAP
* @copyright (c) phpBB Group
* @Оптимизация под WAP: Гутник Игорь ( чел ).
* @简体中文：中文phpBB-WAP团队
* @license http://opensource.org/licenses/gpl-license.php
**/

/**
* 这是一款自由软件, 您可以在 Free Software Foundation 发布的
* GNU General Public License 的条款下重新发布或修改; 您可以
* 选择目前 version 2 这个版本（亦可以选择任何更新的版本，由
* 你喜欢）作为新的牌照.
**/

define('IN_PHPBB', true);
define('ROOT_PATH', './../');
if( !empty($setmodules) )
{
	$file = basename(__FILE__);
	$module['系统']['风格编辑器'] = $file;
	return;
}
require('pagestart.php');
if ( isset($_POST['choose']) )
{
	if ( is_dir(ROOT_PATH . 'styles/' . $_POST['template'] . '/') )
	{
		$template->set_filenames(array('files' => 'admin/admin_template_select.tpl'));
				
		$templates = '';
		
		$dir = @opendir(ROOT_PATH .'styles/'. $_POST['template'] . '/');
    while( $file = @readdir($dir) )
    {
		  if( $file != '.' && $file != '..' && $file != 'images' && !is_file(ROOT_PATH .'styles/'. $_POST['template'] . '/' . $file) && !is_link(ROOT_PATH .'styles/'. $_POST['template'] . '/' . $file) )
		  {
			  $sub_dir = @opendir(ROOT_PATH .'styles/'. $_POST['template'] . '/' . $file);

        while( $sub_file = @readdir($sub_dir) )
        {
          if( $sub_file != '.' && $sub_file != '..' && is_file(ROOT_PATH .'styles/'. $_POST['template'] . '/' . $file . '/' . $sub_file) && !is_link(ROOT_PATH .'styles/'. $_POST['template'] . '/' . $file . '/' . $sub_file) )
		      {
				    $templates .=  '<option value="' . $file . '/' . $sub_file . '">' . $file . '/' . $sub_file . '</option>';
          }
        }
		  }
		  }
   
    $file = '';
		@closedir($dir);
		
		$dir = dir(ROOT_PATH .'styles/'. $_POST['template'] . '/');
		while ( $tpl = $dir->read() )
		{
      if ( is_file(ROOT_PATH .'styles/'. $_POST['template'] . '/' . $tpl) )
			{
				$templates .=  '<option value="' . $tpl . '">' . $tpl . '</option>';
			}
		}
		@closedir($dir);
	
	  $template->assign_vars(array(
			'S_ACTION' => append_sid('admin_template.php'),
			'SUBMIT_NAME' => 'file',
			'FILE_NAME' => $_POST['template'] . '/',
			'HIDDEN_THINGS' => '<input type="hidden" name="directory" value="' . $_POST['template'] . '" />',
			'L_CHOOSE_TEMPLATE' => '选择文件',
			'L_SUBMIT' => '确定',
			
			'S_TEMPLATES' => $templates
		));
	
		$template->pparse('files');
		page_footer();
	}
	else
	{
		trigger_error(GENERAL_ERROR,'没有文件可以编辑');
	}
}
else if ( isset($_POST['file']) )
{
	if ( is_file(ROOT_PATH .'styles/'. $_POST['directory'] . '/' . $_POST['template']) )
	{
		$file_data = @implode('', @file(ROOT_PATH .'styles/'. $_POST['directory'] . '/' . $_POST['template']));
		if ( !empty($file_data) )
		{
			$template->set_filenames(array('edit_file' => 'admin/admin_template_edit.tpl'));
			$template->assign_vars(array(
				'S_ACTION' => append_sid('admin_template.php'),
				'HIDDEN_THINGS' => '<input type="hidden" name="directory" value="' . $_POST['directory'] . '" /><input type="hidden" name="file_name" value="' . $_POST['template'] . '" />',
				'SUBMIT_NAME' => 'edit',
				'FILE_NAME' => $_POST['directory'] . '/' . $_POST['template'],
				'FILE_DATA' => htmlspecialchars(trim($file_data)),
				'L_EDIT_TEMPLATE' => '编辑主题',
				'L_SUBMIT' => '确定',
				'L_RESET' => '重置'
			));
			$template->pparse('edit_file');
			page_footer();
		}
		else
		{
			trigger_error('没有打开！');
		}
	}
	else
	{
		trigger_error('不能编辑附件！');
	}
}
else if ( isset($_POST['edit']) )
{
	if ( is_file(ROOT_PATH .'styles/'. $_POST['directory'] . '/' . $_POST['file_name']) )
	{
		$fp = fopen(ROOT_PATH .'styles/'. $_POST['directory'] . '/' . $_POST['file_name'], 'w');
		if ( $fp )
		{
      $file_data = stripslashes(trim($_POST['file_data']));
      $file_data = str_replace ("\r", "", $file_data);

      fwrite($fp, $file_data, strlen($file_data));
			fclose($fp);
			$message = '确定修改文件？<br /><br /><a href="'. append_sid('admin_template.php') . '">确定</a><br /><br /><a href="' . append_sid('index.php?pane=right') . '">取消</a>';

      trigger_error($message);
		}
		else
		{
		trigger_error('不能编辑附件！');
		}
	}
}
		
$template->set_filenames(array('template' => 'admin/admin_template_select.tpl'));
$themes = '';
$dir = dir(ROOT_PATH . '/styles/');
while ( $tpl = $dir->read() )
{
	if ( !strstr($tpl, '.') )
	{
		$themes .= '<option value="' . $tpl . '">' . $tpl . '</option>';
	}
}
$template->assign_vars(array(
	'S_ACTION' => append_sid('admin_template.php'),
	'SUBMIT_NAME' => 'choose',
	'L_CHOOSE_TEMPLATE' => '选择文件',
	'L_SUBMIT' => '确定',
	
	'S_TEMPLATES' => $themes
));
$dir->close();
$template->pparse('template');
page_footer();
?>
