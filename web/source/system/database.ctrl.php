<?php 
/**
 * 数据库相关操作
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
//防止30秒运行超时的错误（Maximum execution time of 30 seconds exceeded).
set_time_limit(0);

load()->func('file');
load()->model('cloud');
load()->func('db');

$dos = array('backup', 'restore', 'trim', 'optimize', 'run');
$do = in_array($do, $dos) ? $do : 'backup';

global $_W;
//备份
if($do == 'backup') {
	$_W['page']['title'] = '备份 - 数据库 - 常用系统工具 - 系统管理';
	
	if($_GPC['status']) {
		
		if (empty($_W['setting']['copyright']['status'])) {
			message('为了保证备份数据完整请关闭站点后再进行此操作', url('system/site'), 'error');
		}
		
		//获取系统数据库中所有表
		$sql = "SHOW TABLE STATUS LIKE '{$_W['config']['db']['tablepre']}%'";
		$tables = pdo_fetchall($sql);
		if(empty($tables)) {
			message('数据已经备份完成', url('system/database/backup/'), 'success');
		}
		
		//设置备份文件的卷数和文件名中随机数。
		$series = $_GPC['series'] ? $_GPC['series'] : 1;
		$prefix = $_GPC['prefix'] ? $_GPC['prefix'] : random(10);
		//设置上卷结束时的表名
		$start_table = $_GPC['table'] ? $_GPC['table'] : '';
	
		//备份目录
		if($_GPC['start']) {
			$suffix =  TIMESTAMP . '_' . random(8);
			$bakdir = IA_ROOT .'/data/backup/'.$suffix;
			$result = mkdirs($bakdir);		
		} else {
			$suffix = $_GPC['suffix'];
			$bakdir = IA_ROOT .'/data/backup/'.$suffix;
		}
				
		//一次让查询300条记录
		$size = 300;
		//设置备份文件的大小
		$volumn = 1024 * 1024 * 2;
		//设置判断上卷结束时表名的标识
		$catch = empty($start_table) ? true : false;
		$dump = '';
		
		foreach($tables as $table) {
			//抛出表名
			$table = array_shift($table);
			//如果开始有表，且表名相同	
			if(!empty($start_table) && $table == $start_table) {
				$catch = true;
			}
			//查询上卷结束时的表名，没找到则跳出本次循环，进入下次循环
			if(!$catch) {
				continue;
			}
			if(!empty($dump)) {
				$dump .= "\n\n";
			}
			// 枚举所有表的创建语句
			if($table != $start_table) {
				$dump .= "DROP TABLE IF EXISTS {$table};\n";
				$sql = "SHOW CREATE TABLE {$table}";
				$row = pdo_fetch($sql);
				$dump .= $row['Create Table'];
				$dump .= ";\n\n";
			}
			//枚举所有表列名的详细信息
			$fields = pdo_fetchall("SHOW FULL COLUMNS FROM {$table}", array(), 'Field');
			if(empty($fields)) {
				continue;
			}
			//设置查询表中记录的开始标识
			$index = 0;
			if(!empty($_GPC['index'])) {
				$index = $_GPC['index'];
				$_GPC['index'] = 0;
			}
			//枚举所有表的INSERT语句
			while(true) {
				$start = $index * $size;
				$sql = "SELECT * FROM {$table} LIMIT {$start}, {$size}";
				$result = pdo_fetchall($sql);
				if(!empty($result)) {
					$tmp = '';
					foreach($result as $row) {
						$tmp .= '(';
						foreach($row as $k => $v) {
							$value = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $v); 
							$tmp .= "'" . $value . "',";
						}
						$tmp = rtrim($tmp, ',');
						$tmp .= "),\n";
					}
					$tmp = rtrim($tmp, ",\n");
					$dump .= "INSERT INTO {$table} VALUES \n{$tmp};\n";
					
					if(strlen($dump) > $volumn) {
						$bakfile = $bakdir . "/volume-{$prefix}-{$series}.sql";
						$dump .= "\n\n";
						file_put_contents($bakfile, $dump);
						$current = array();
						$series++;
						$current['table'] = $table;
						$current['index'] = $index + 1;
						$current['series'] = $series;
						$current['prefix'] = $prefix;
						$current['suffix'] = $suffix;
						$current['status'] = 1;
						$current['start'] = 0;
						$current_series = $series-1;
						message('正在导出数据, 请不要关闭浏览器, 当前第 ' . $current_series . ' 卷.', url('system/database/backup/',$current));
					}
					
				}
				
				if(empty($result) || count($result) < $size) {
					break;
				}	
				$index++;
			}		
		}
			
		//结束标识	
		$bakfile = $bakdir . "/volume-{$prefix}-{$series}.sql";
		$dump .= "\n\n----WeEngine MySQL Dump End";
		file_put_contents($bakfile, $dump);
		message('数据已经备份完成', url('system/database/backup/'), 'success');		
	}
}

//还原
if($do == 'restore') {
	$_W['page']['title'] = '还原 - 数据库 - 常用系统工具 - 系统管理';
	$ds = array();
	$path = IA_ROOT . '/data/backup/';
//获取备份数据
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
							$ds[$bakdir] = $row;
							continue;
						}
					}
				}
				rmdirs($path . $bakdir);
			}
		}
	}
	
//还原备份
	if($_GPC['r']) {
		$r = $_GPC['r'];
		if($ds[$r]) {
			$row = $ds[$r];
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
			$restore = json_decode(base64_decode($_GPC['__restore']), true) ? $restore = json_decode(base64_decode($_GPC['__restore']), true) : array();
			$restore['restore_name'] = $restore['restore_name'] ? $restore['restore_name'] : $row['bakdir'];
			$restore['restore_volume'] = $restore['restore_volume'] ? $restore['restore_volume'] : 1;
			$restore['restore_prefix'] = $restore['restore_prefix'] ? $restore['restore_prefix']: $prefix;
			if($ds[$restore['restore_name']]) {
				if($ds[$restore['restore_name']]['volume'] < $restore['restore_volume']) {
					isetcookie('__restore', '', -1000);
					message('成功恢复数据备份. 可能还需要你更新缓存.', url('system/database/restore'));
				} else {
					$sql = file_get_contents($path .$restore['restore_name'] . "/volume-{$restore['restore_prefix']}-{$restore['restore_volume']}.sql");
					pdo_run($sql);
					$volume = $restore['restore_volume'];
					$restore['restore_volume'] ++;
					isetcookie('__restore', base64_encode(json_encode($restore)));
					message('正在恢复数据备份, 请不要关闭浏览器, 当前第 ' . $volume . ' 卷.', url('system/database/restore',array('r'=>$r)));
				}
			} else {
				message('非法访问', 'error');
			}
		}
	}				

//删除备份	
	if($_GPC['d']) {
		$d = $_GPC['d'];
		if($ds[$d]) {
			rmdirs($path . $d);
			message('删除备份成功.', url('system/database/restore'));
		}
	}
	
}

//数据库结构整理
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
 * @@todo 没有该控制器
	$r = cloud_prepare();
	if(is_error($r)) {
		message($r['message'], url('cloud/profile'), 'error');
	}
	*/
	
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
					$diff[$tablename]['name'] = $value['tablename'];
					foreach ($temp['fields']['less'] as $key=>$value) {
						$diff[$tablename]['fields'][] = $value;
					}
				}
				if (!empty($temp['indexes']['less'])) {
					$diff[$tablename]['name'] = $value['tablename'];
					foreach ($temp['indexes']['less'] as $key=>$value) {
						$diff[$tablename]['indexes'][] = $value;
					}
				}
			}
		}
	}
}
//优化
if($do == 'optimize') {
	$_W['page']['title'] = '优化 - 数据库 - 常用系统工具 - 系统管理';
	$sql = "SHOW TABLE STATUS LIKE '{$_W['config']['db']['tablepre']}%'";
	$tables = pdo_fetchall($sql);
	$totalsize = 0;
	$optimize_table = array();
	foreach($tables as $table) {
		if ($table['Engine'] == 'InnoDB') {
			continue;
		}
		if(!empty($table) && !empty($table['Data_free'])) {
			$row = array();
			$row['title'] = $table['Name'];
			$row['type'] = $table['Engine'];
			$row['rows'] = $table['Rows'];
			$row['data'] = sizecount($table['Data_length']);
			$row['index'] = sizecount($table['Index_length']);
			$row['free'] = sizecount($table['Data_free']);
			$optimize_table[$row['title']] = $row;
		}
	}

	if(checksubmit()) {
		foreach($_GPC['select'] as $tablename) {
			if(!empty($optimize_table[$tablename])) {
				$sql = "OPTIMIZE TABLE {$tablename}";
				pdo_fetch($sql);
			}
		}
		message('数据表优化成功.', 'refresh');
	}
}
//运行SQL
if($do == 'run') {
	$_W['page']['title'] = '运行SQL - 数据库 - 常用系统工具 - 系统管理';
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

