<?php 
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 规则查询 `rule`
 * @param string $condition 查询条件 WHERE 后内容, eg: $condition='id=:id, acid=:acid';
 * @param array $params 查询参数, eg: array(':id'=>$id,':acid'=>$acid);
 * @param int $pindex 当前页码, 0 全部记录
 * @param int $psize 分页大小
 * @param int $total 总记录数
 * @return array
 */
function reply_search($condition = '', $params = array(), $pindex = 0, $psize = 10, &$total = 0) {
	if (!empty($condition)) {
		$where = "WHERE {$condition}";
	}
	$sql = 'SELECT * FROM ' . tablename('rule') . $where . " ORDER BY status DESC, displayorder DESC, id DESC";
	if ($pindex > 0) {
		// 需要分页
		$start = ($pindex - 1) * $psize;
		$sql .= " LIMIT {$start},{$psize}";
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('rule') . $where, $params);
	}
	return pdo_fetchall($sql, $params);
}

/**
 * 查询单条规则及其下的所有关键字
 * @param number $id
 * @return array array('rule'=>$rule,'keyword'=>array($rule_key,...))
 */
function reply_single($id) {
	$result = array();
	$id = intval($id);
	$result = pdo_fetch("SELECT * FROM " . tablename('rule') . " WHERE id = :id", array(':id' => $id));
	if (empty($result)) {
		return $result;
	}
	$result['keywords'] = pdo_fetchall("SELECT * FROM " . tablename('rule_keyword') . " WHERE rid = :rid", array(':rid' => $id));
	return $result;
}

/**
 * 从 `rule_keyword` 查询满足条件的所有规则关键字 
 * @param string $condition 查询条件 WHERE 后内容, eg: $condition='id=:id, acid=:acid';
 * @param array $params 查询参数, eg: array(':id'=>$id,':acid'=>$acid);
 * @param int $pindex 当前页码, 0 全部记录.
 * @param int $psize 分页大小
 * @param int $total 总记录数
 * @return array
 */
function reply_keywords_search($condition = '', $params = array(), $pindex = 0, $psize = 10, &$total = 0) {
	if (!empty($condition)) {
		$where = " WHERE {$condition} ";
	}
	$sql = 'SELECT * FROM ' . tablename('rule_keyword') . $where . ' ORDER BY displayorder DESC, `type` ASC, id DESC';
	if ($pindex > 0) {
		// 需要分页
		$start = ($pindex - 1) * $psize;
		$sql .= " LIMIT {$start},{$psize}";
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('rule_keyword') . $where, $params);
	}
	$result = pdo_fetchall($sql, $params);
	if (!empty($result)) {
		foreach ($result as $key => $val) {
			$containtypes = pdo_get('rule', array('id' => $val['rid']), array('containtype'));
			if (!empty($containtypes)) {
				$containtype = explode(',', $containtypes['containtype']);
				$containtype = array_filter($containtype);
			} else {
				$containtype = array();
			}
			$result[$key]['reply_type'] = $containtype;
		}
	} else {
		$result = array();
	}
	return $result;
}

/**
 * 查询某一关键字回复中所有回复内容
 * @param int $rid  要查询的rule规则ID
 * @param array $params  查询参数
 * @return array
 */
function reply_contnet_search($rid = 0) {
	$result = array();
	$result['sum'] = 0;
	$rid = intval($rid);
	if(empty($rid)){
		return $result;
	}
	$modules = array('basic', 'images', 'news', 'music', 'voice', 'video', 'wxcard');
	$params = array(':rid' => $rid);
	foreach($modules as $key => $module){
		$sql = 'SELECT COUNT(*) FROM ' . tablename($module.'_reply') . ' WHERE `rid` = :rid';
		$result[$module] = pdo_fetchcolumn($sql, $params);
		$result['sum'] += $result[$module];
	}
	return $result;
}