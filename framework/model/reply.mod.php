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
	$result = pdo_get('rule', array('id' => $id));
	if (empty($result)) {
		return $result;
	}
	$result['keywords'] = pdo_getall('rule_keyword', array('rid' => $id));
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
	if (empty($rid)) {
		return $result;
	}
	$modules = array('basic', 'images', 'news', 'music', 'voice', 'video', 'wxcard');
	$params = array(':rid' => $rid);
	foreach ($modules as $key => $module) {
		$sql = 'SELECT COUNT(*) FROM ' . tablename($module.'_reply') . ' WHERE `rid` = :rid';
		$result[$module] = pdo_fetchcolumn($sql, $params);
		$result['sum'] += $result[$module];
	}
	return $result;
}

/**
 * 系统预定义的所有常用服务.
 * @return array
 */
function reply_predefined_service() {
	$predefined_service = array(
			'weather.php' => array(
					'title' => '城市天气',
					'description' => '"城市名+天气", 如: "北京天气"',
					'keywords' => array(
							array('3', '^.+天气$')
					)
			),
			'baike.php' => array(
					'title' => '百度百科',
					'description' => '"百科+查询内容" 或 "定义+查询内容", 如: "百科姚明", "定义自行车"',
					'keywords' => array(
							array('3', '^百科.+$'),
							array('3', '^定义.+$'),
					)
			),
			'translate.php' => array(
					'title' => '即时翻译',
					'description' => '"@查询内容(中文或英文)"',
					'keywords' => array(
							array('3', '^@.+$'),
					)
			),
			'calendar.php' => array(
					'title' => '今日老黄历',
					'description' => '"日历", "万年历", "黄历"或"几号"',
					'keywords' => array(
							array('1', '日历'),
							array('1', '万年历'),
							array('1', '黄历'),
							array('1', '几号'),
					)
			),
			'news.php' => array(
					'title' => '看新闻',
					'description' => '"新闻"',
					'keywords' => array(
							array('1', '新闻'),
					)
			),
			'express.php' => array(
					'title' => '快递查询',
					'description' => '"快递+单号", 如: "申通1200041125"',
					'keywords' => array(
							array('3', '^(申通|圆通|中通|汇通|韵达|顺丰|EMS) *[a-z0-9]{1,}$')
					)
			),
	);
	return $predefined_service;
}

/**
 * 获取本站点当前存在的所有预定义常用服务的apiurl
 * @return array
 */
function reply_getall_current_apiurls() {
	$apiurls = array();
	$predefined_service = reply_predefined_service();
	$apis = implode('\',\'', array_keys($predefined_service));
	$apis = "'{$apis}'";
	$sql = 'SELECT DISTINCT `apiurl` FROM ' . tablename('userapi_reply') . ' AS `e` LEFT JOIN ' . tablename('rule') . " AS `r` ON (`e`.`rid`=`r`.`id`) WHERE `r`.`uniacid`='0' AND `apiurl` IN ({$apis})";
	$apiurls = pdo_fetchall($sql);
	return $apiurls;
}