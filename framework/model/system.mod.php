<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 获取包括系统及模块所有的菜单权限
 * 
 */
function system_menu_permission_list($role = '') {
	$system_menu = cache_load('system_frame');
	if(empty($system_menu)) {
		cache_build_frame_menu();
		$system_menu = cache_load('system_frame');
	}
	//根本不同的角色得到不同的菜单权限
	if ($role == ACCOUNT_MANAGE_NAME_OPERATOR) {
		unset($system_menu['appmarket']);
		unset($system_menu['adviertisement']);
		unset($system_menu['system']);
	} if ($role == ACCOUNT_MANAGE_NAME_OPERATOR) {
		unset($system_menu['appmarket']);
		unset($system_menu['adviertisement']);
	}
	return $system_menu;
}
/**
 * 获得数据库备份目录下的数据库备份文件数组
 * @return array;
 */
function system_database_backup() {
	$path = IA_ROOT . '/data/backup/'; 
	load()->func('file');
	$reduction = array();
	if (!is_dir($path)) {
		return array();
	}
	if ($handle = opendir($path)) {
		while (false !== ($bakdir = readdir($handle))) {
			if ($bakdir == '.' || $bakdir == '..') {
				continue;
			}
			if (preg_match('/^(?P<time>\d{10})_[a-z\d]{8}$/i', $bakdir, $match)) {
				$time = $match['time'];
				//获取随机字符串
				if ($handle1= opendir($path . $bakdir)) {
					while (false !== ($filename = readdir($handle1))) {
						if ($filename == '.' || $filename == '..') {
							continue;
						}
						if (preg_match('/^volume-(?P<prefix>[a-z\d]{10})-\d{1,}\.sql$/i', $filename, $match1)) {
							$volume_prefix = $match1['prefix'];
							if (!empty($volume_prefix)) {
								break;
							}
						}
					}
				}
				for ($i = 1;;) {
					$last = $path . $bakdir . "/volume-{$volume_prefix}-{$i}.sql";
					$i++;
					$next = $path . $bakdir . "/volume-{$volume_prefix}-{$i}.sql";
					if (!is_file($next)) {
						break;
					}
				}
				if (is_file($last)) {
					$fp = fopen($last, 'r');
					fseek($fp, -27, SEEK_END);
					$end = fgets($fp);
					fclose($fp);
					if ($end == '----WeEngine MySQL Dump End') {
						$row = array(
								'bakdir'=> $bakdir,
								'time'=> $time,
								'volume'=> $i - 1
						);
						$reduction[$bakdir] = $row;
						continue;
					}
				}
			}
			rmdirs($path . $bakdir);
		}
	}
	return $reduction;
}
/**
 * 还原数据库备份目录下的一个备份数据
 * @return array;
 */
function system_database_restore($reduction, $restore) {
	$path = IA_ROOT . '/data/backup/';
	$restore_dirname = $restore['restore_dirname'];
	if ($reduction[$restore_dirname]) {
		$row = $reduction[$restore_dirname];
		$dir = $path . $row['bakdir'];
		//获取随机字符串
		if ($handle1= opendir($dir)) {
			while (false !== ($filename = readdir($handle1))) {
				if ($filename == '.' || $filename == '..') {
					continue;
				}
				if (preg_match('/^volume-(?P<prefix>[a-z\d]{10})-\d{1,}\.sql$/i', $filename, $match1)) {
					$volume_prefix = $match1['prefix'];
					break;
				}
			}
		}
		//还原备份文件的前缀
		if (empty($restore['restore_volume_prefix'])) {
			$restore_volume_prefix = $volume_prefix;
		} else {
			$restore_volume_prefix = $restore['restore_volume_prefix'];
		}
		//当前还原备份文件的卷数
		$restore_volume_sizes = max(1, intval($restore['restore_volume_sizes']));
		if ($reduction[$restore_dirname]) {
			if ($reduction[$restore_dirname]['volume'] < $restore_volume_sizes) {
				itoast('成功恢复数据备份. 可能还需要你更新缓存.', url('system/database/restore'), 'success');
			} else {
				$sql = file_get_contents($path .$restore_dirname . "/volume-{$restore_volume_prefix}-{$restore_volume_sizes}.sql");
				pdo_run($sql);
				$volume_sizes = $restore_volume_sizes;
				$restore_volume_sizes ++;
				$restore = array (
					'restore_dirname' => $restore_dirname,
					'restore_volume_prefix' => $restore_volume_prefix,
					'restore_volume_sizes' => $restore_volume_sizes,
				);
				message('正在恢复数据备份, 请不要关闭浏览器, 当前第 ' . $volume_sizes . ' 卷.', url('system/database/restore',$restore), 'success');
			}
		} else {
			itoast('非法访问', '','error');
		}
	}
}
/**
 * 删除数据库备份目录下的某个备份数据
 * @return array;
 */
function system_database_delete($reduction, $delete_dirname) {
	$path = IA_ROOT . '/data/backup/';
	if ($reduction[$delete_dirname]) {
		rmdirs($path . $delete_dirname);
		itoast('删除备份成功.', url('system/database/restore'), 'success');
	}
}