<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->func('file');

/**
 * 导出数据库数据 `export`
 * @param array $params 备份数据库文件信息
 * @return array $backup_file_info 备份数据库文件信息
 */
function export_database_data($params = array()) {
	global $_W;
	$backup_file_info = array();
	//获取备份数据库中所有表名
	$sql = "SHOW TABLE STATUS LIKE '{$_W['config']['db']['tablepre']}%'";
	$tablenameables = pdo_fetchall($sql);
	if(empty($tablenameables)) {
		return $backup_file_info;
	}
	//创建备份目录
	if(empty($params)) {
		do {
			$bakdir = IA_ROOT . '/data/backup/' . TIMESTAMP . '_' . random(8);
		} while(is_dir($bakdir));
		mkdirs($bakdir);
	} else {
		$bakdir = $params['bakdir'];
	}
	
	$size = 300;
	$volumn = 1024 * 1024 * 2;
	
	$series = 1;
	$prefix = random(10);
	if(!empty($params)) {
		$series = $params['series'];
		$prefix = $params['prefix'];
	}
	$dump = '';
	$catch = false;
	if(empty($params)) {
		$catch = true;
	}
	foreach($tablenameables as $tablename) {
		$tablename = array_shift($tablename);
		if(!empty($params) && $tablename == $params['table']) {
			$catch = true;
		}
		if(!$catch) {
			continue;
		}
		if(!empty($dump)) {
			$dump .= "\n\n";
		}
		//获取创建表的sql语句
		if($tablename != $params['table']) {
			$dump .= "DROP TABLE IF EXISTS {$tablename};\n";
			$sql = "SHOW CREATE TABLE {$tablename}";
			$row = pdo_fetch($sql);
			$dump .= $row['Create Table'];
			$dump .= ";\n\n";
		}
		$fields = pdo_fetchall("SHOW FULL COLUMNS FROM {$tablename}", array(), 'Field');
		if(empty($fields)) {
			continue;
		}
		$index = 0;
		if(!empty($params)) {
			$index = $params['index'];
			$params = array();
		}
		while(true) {
			$start = $index * $size;
			$sql = "SELECT * FROM {$tablename} LIMIT {$start}, {$size}";
			$result = pdo_fetchall($sql);
			if(!empty($result)) {
				$tablenamemp = '';
				foreach($result as $row) {
					$tablenamemp .= '(';
					foreach($row as $k => $v) {
						$tablenamemp .= "'" . export_replace_special_character($v) . "',";
					}
					$tablenamemp = rtrim($tablenamemp, ',');
					$tablenamemp .= "),\n";
				}
				$tablenamemp = rtrim($tablenamemp, ",\n");
				$dump .= "INSERT INTO {$tablename} VALUES \n{$tablenamemp};\n";
				if(strlen($dump) > $volumn) {
					$bakfile = $bakdir . "/volume-{$prefix}-{$series}.sql";
					$dump .= "\n\n";
					file_put_contents($bakfile, $dump);
					$series++;
					$backup_file_info['table'] = $tablename;
					$backup_file_info['index'] = $index + 1;
					$backup_file_info['series'] = $series;
					$backup_file_info['prefix'] = $prefix;
					$backup_file_info['bakdir'] = $bakdir;
					return $backup_file_info;
				}
			}
			if(empty($result) || count($result) < $size) {
				break;
			}
			$index++;
		}
	}
	
	$bakfile = $bakdir . "/volume-{$prefix}-{$series}.sql";
	$dump .= "\n\n----WeEngine MySQL Dump End";
	file_put_contents($bakfile, $dump);
	return $backup_file_info;
}

/**
 * 替换特殊字符
 * @param stirng $character  备份数据库的表中记录
 * @return stirng $character 备份数据库的表中记录
 */
function export_replace_special_character($character) {
	return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $character);
} 