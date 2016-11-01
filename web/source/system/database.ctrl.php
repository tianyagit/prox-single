<?php 
/**
 * 系统管理--常用系统工具--数据库相关操作
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
//防止30秒运行超时的错误（Maximum execution time of 30 seconds exceeded).
set_time_limit(0);

load()->func('file');
load()->model('cloud');
load()->model('export');
load()->func('db');

$dos = array('backup', 'restore', 'trim', 'optimize', 'run');
$do = in_array($do, $dos) ? $do : 'backup';

/**
 * 备份
 */
if($do == 'backup') {
	$_W['page']['title'] = '备份 - 数据库 - 系统管理';
	if(checksubmit()) {
		if (empty($_W['setting']['copyright']['status'])) {
			message('为了保证备份数据完整请关闭站点后再进行此操作', url('system/site'), 'error');
		}
		$backup_file_info = export_database_data();
		if(!empty($backup_file_info)) {
			isetcookie('__backup_file_info', base64_encode(json_encode($backup_file_info)));
			message('正在导出数据, 请不要关闭浏览器, 当前第 1 卷.', url('system/database/backup'));
		} else {
			message('数据已经备份完成', 'refresh');
		}
	}
	if($_GPC['__backup_file_info']) {
		$current = json_decode(base64_decode($_GPC['__backup_file_info']), true);
		$backup_file_info = export_database_data($current);
		if(!empty($backup_file_info)) {
			isetcookie('__backup_file_info', base64_encode(json_encode($backup_file_info)));
			message('正在导出数据, 请不要关闭浏览器, 当前第 ' . $current['series'] . ' 卷.', url('system/database/backup'));
		} else {
			isetcookie('__backup_file_info', '', -1000);
			message('数据已经备份完成', 'refresh');
		}
	}
}

/**
 *还原 
 */
if($do == 'restore') {
	$_W['page']['title'] = '还原 - 数据库 - 系统管理';
	$tablesinfo = array();
	$path = IA_ROOT . '/data/backup/';
	if (is_dir($path)) {
		if ($handle = opendir($path)) {
			while (false !== ($bakdir = readdir($handle))) {
				if($bakdir == '.' || $bakdir == '..') {
					continue;
				}
				if(preg_match('/^(?P<time>\d{10})_[a-z\d]{8}$/i', $bakdir, $match)) {
					$time = $match['time'];
					//获取随机字符串
					if($handle1= opendir($path . $bakdir)) {
						while(false !== ($filename = readdir($handle1))) {
							if($filename == '.' || $filename == '..') {
								continue;
							}
							if(preg_match('/^volume-(?P<prefix>[a-z\d]{10})-\d{1,}\.sql$/i', $filename, $match1)) {
								$prefix = $match1['prefix'];
								if(!empty($prefix)) {
									break;
								}
							}
						}
					}
					for($i = 1;;) {
						$last = $path . $bakdir . "/volume-{$prefix}-{$i}.sql";
						$i++;
						$next = $path . $bakdir . "/volume-{$prefix}-{$i}.sql";
						if(!is_file($next)) {
							break;
						}
					}
					if(is_file($last)) {
						$fp = fopen($last, 'r');
						fseek($fp, -27, SEEK_END);
						$end = fgets($fp);
						fclose($fp);
						if($end == '----WeEngine MySQL Dump End') {
							$row = array();
							$row['bakdir'] = $bakdir;
							$row['time'] = $time;
							$row['volume'] = $i - 1;
							$tablesinfo[$bakdir] = $row;
							continue;
						}
					}
				}
				rmdirs($path . $bakdir);
			}
		}
	}

	if($_GPC['r']) {
		$r = $_GPC['r'];
		if($tablesinfo[$r]) {
			$row = $tablesinfo[$r];
			$dir = $path . $row['bakdir'];
			//获取随机字符串
			if($handle1= opendir($dir)) {
				while(false !== ($filename = readdir($handle1))) {
					if($filename == '.' || $filename == '..') {
						continue;
					}
					if(preg_match('/^volume-(?P<prefix>[a-z\d]{10})-\d{1,}\.sql$/i', $filename, $match1)) {
						$prefix = $match1['prefix'];
						break;
					}
				}
			}
			//执行第一卷
			$sql = file_get_contents($path . $row['bakdir'] . "/volume-{$prefix}-1.sql");
			pdo_run($sql);
			if($row['volume'] == 1) {
				message('成功恢复数据备份. 可能还需要你更新缓存.', url('system/database/restore'));
			} else {
				$restore = array();
				$restore['restore_name'] = $r;
				$restore['restore_volume'] = 2;
				$restore['restore_prefix'] = $prefix;
				isetcookie('__restore', base64_encode(json_encode($restore)));
				message('正在恢复数据备份, 请不要关闭浏览器, 当前第 1 卷.', url('system/database/restore'));
			}
		}
	}
		
	if($_GPC['__restore']) {
		$restore = json_decode(base64_decode($_GPC['__restore']), true);
		if($tablesinfo[$restore['restore_name']]) {
			if($tablesinfo[$restore['restore_name']]['volume'] < $restore['restore_volume']) {
				isetcookie('__restore', '', -1000);
				message('成功恢复数据备份. 可能还需要你更新缓存.', url('system/database/restore'));
			} else {
				$sql = file_get_contents($path .$restore['restore_name'] . "/volume-{$restore['restore_prefix']}-{$restore['restore_volume']}.sql");
				pdo_run($sql);
				$volume = $restore['restore_volume'];
				$restore['restore_volume'] ++;
				isetcookie('__restore', base64_encode(json_encode($restore)));
				message('正在恢复数据备份, 请不要关闭浏览器, 当前第 ' . $volume . ' 卷.', url('system/database/restore'));
			}
		} else {
			message('非法访问', 'error');
		}
	}	
	
	if($_GPC['d']) {
		$d = $_GPC['d'];
		if($tablesinfo[$d]) {
			rmdirs($path . $d);
			message('删除备份成功.', url('system/database/restore'));
		}
	}
}

/**
 * 数据库结构整理
 */
if($do == 'trim') {
	if ($_W['ispost']) {
		$type = $_GPC['type'];
		$data = $_GPC['data'];
		$table = $_GPC['table'];
		if ($type == 'field') {
			$sql = "ALTER TABLE `$table` DROP `$data`";
			if (false !== pdo_query($sql, $params)) {
				exit('success');
			}
		} elseif ($type == 'index') {
			$sql = "ALTER TABLE `$table` DROP INDEX `$data`";
			if (false !== pdo_query($sql, $params)) {
				exit('success');
			}
		}
		exit();
	}

	/**
	 * @@todo 没有这个控制器。
	$r = cloud_prepare();
	if(is_error($r)) {
		message($r['message'], url('cloud/profile'), 'error');
	}
	 *
	 **/
	$upgrade = cloud_schema();
	$schemas = $upgrade['schemas'];
	/*
	 * $schemas 是存在差异的数据库表
	 * 遍历$schemas, 读取本地数据库. 然后使用compare
	*/
	
	if (!empty($schemas)) {

		foreach ($schemas as $key=>$value) {
			$tablename =  substr($value['tablename'], 4);
			$struct = db_table_schema(pdo(), $tablename);
			if (!empty($struct)) {
				$temp = db_schema_compare($schemas[$key],$struct);
				if (!empty($temp['fields']['less'])) {
					$different[$tablename]['name'] = $value['tablename'];
					foreach ($temp['fields']['less'] as $key=>$value) {
						$different[$tablename]['fields'][] = $value;
					}
				}
				if (!empty($temp['indexes']['less'])) {
					$different[$tablename]['name'] = $value['tablename'];
					foreach ($temp['indexes']['less'] as $key=>$value) {
						$different[$tablename]['indexes'][] = $value;
					}
				}
			}
		}
	}
}

/**
 * 优化
 */
if($do == 'optimize') {
	
	$_W['page']['title'] = '优化 - 数据库 - 系统管理';
	$sql = "SHOW TABLE STATUS LIKE '{$_W['config']['db']['tablepre']}%'";
	$tables = pdo_fetchall($sql);
	$totalsize = 0;
	$tablesinfo = array();
	foreach($tables as $tableinfo) {
		if ($tableinfo['Engine'] == 'InnoDB') {
			continue;
		}
		if(!empty($tableinfo) && !empty($tableinfo['Data_free'])) {
			$row = array();
			//表名称
			$row['title'] = $tableinfo['Name'];
			//表索引
			$row['type'] = $tableinfo['Engine'];
			//表中的行数
			$row['rows'] = $tableinfo['Rows'];
			//整个表的数据量(单位：字节)
			$row['data'] = sizecount($tableinfo['Data_length']);
			//索引占用磁盘的空间大小
			$row['index'] = sizecount($tableinfo['Index_length']);
			//对于MyISAM引擎，标识已分配，但现在未使用的空间，并且包含了已被删除行的空间
			$row['free'] = sizecount($tableinfo['Data_free']);
			$tablesinfo[$row['title']] = $row;
		}
	}
	
	if(checksubmit()) {
		foreach($_GPC['select'] as $tablename) {
			if(!empty($tablesinfo[$tablename])) {
				$sql = "OPTIMIZE TABLE {$tablename}";
				pdo_fetch($sql);
			}
		}
		message('数据表优化成功.', 'refresh');
	}
}

/**
 * 运行SQL
 */
if($do == 'run') {
	
	$_W['page']['title'] = '运行SQL - 数据库 - 系统管理';
	if (!DEVELOPMENT) {
		message('请先开启开发模式后再使用此功能', referer(), 'info');
	}
	
	if(checksubmit()) {
		$sql = $_POST['sql'];
		pdo_run($sql);
		message('查询执行成功.', 'refresh');
	}
}

template('system/database');

