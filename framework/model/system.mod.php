<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->func('file');

	function system_table_insert($table,$start,$size) {
				$data = '';
				$tmp = '';
				$sql = "SELECT * FROM {$table} LIMIT {$start}, {$size}";
				$result = pdo_fetchall($sql);
				if (!empty($result)) {
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
					$data .= "INSERT INTO {$table} VALUES \n{$tmp};\n";
					$datas = array (
								'data' => $data,
								'result' => $result
							);
					return $datas;
				} else {
					return false ;
				}
	}
