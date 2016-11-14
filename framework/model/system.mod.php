<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 文件大小
 * @param string $dir 文件的路径
 * @return int $size 文件大小
 */
function dir_size($dir) {
	$size = 0;
	if(is_dir($dir)) {
		$handle = opendir($dir);
		while (false !== ($entry = readdir($handle))) {
			if($entry != '.' && $entry != '..') {
				if(is_dir("{$dir}/{$entry}")) {
					$size += dir_size("{$dir}/{$entry}");
				} else {
					$size += filesize("{$dir}/{$entry}");
				}
			}
		}
		closedir($handle);
	}
	return $size;
}

/**
 *字节数转成 bit
 * @param string $str 字节数
 * @return float 
 */
function parse_size($str) {
	if(strtolower($str[strlen($str) -1]) == 'k') {
		return floatval($str) * 1024;
	}
	if(strtolower($str[strlen($str) -1]) == 'm') {
		return floatval($str) * 1048576;
	}
	if(strtolower($str[strlen($str) -1]) == 'g') {
		return floatval($str) * 1073741824;
	}
}