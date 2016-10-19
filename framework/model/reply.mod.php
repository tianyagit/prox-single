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
	$sql = 'SELECT * FROM ' . tablename('rule') . $where . " ORDER BY status DESC, displayorder DESC, id ASC";
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
	$sql = 'SELECT * FROM ' . tablename('rule_keyword') . $where . ' ORDER BY displayorder DESC, `type` ASC, id DESC LIMIT 3';
	if ($pindex > 0) {
		// 需要分页
		$start = ($pindex - 1) * $psize;
		$sql .= " LIMIT {$start},{$psize}";
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('rule_keyword') . $where, $params);
	}
	return pdo_fetchall($sql, $params);
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
/**
	*获取整条字符串汉字拼音首字母
	* @param string $zh  要获取拼音的字符串
	* @return string
*/
function pinyin_long($zh){
	$return = "";
	$s1 = iconv("UTF-8","gb2312", $zh);
	$s2 = iconv("gb2312","UTF-8", $s1);
	if($s2 == $zh){$zh = $s1;}
	for($i = 0; $i < strlen($zh); $i++){
		$s1 = substr($zh,$i,1);
		$asc_value = ord($s1);
		if($asc_value > 160){
			$s2 = substr($zh,$i++,2);
			$return .= get_first_char($s2);
		}else{
			$return .= $s1;
		}
	}
	return $return;
}

/**
 * 获取单个汉字拼音首字母。注意:此处不要纠结。汉字拼音是没有以U和V开头的
 *	@param string $var 要获取拼音首字母的汉字字符串
 *  @return string
 */
function get_first_char($var){
	$asc_value = ord($var{0});
	if($asc_value >= ord("A") and $asc_value <= ord("z") )return strtoupper($var{0});
	$s1 = iconv("UTF-8","gb2312", $var);
	$s2 = iconv("gb2312","UTF-8", $s1);
	if($s2 == $var){$s = $s1;}else{$s = $var;}
	$asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
	if($asc >= -20319 and $asc <= -20284) return "A";
	if($asc >= -20283 and $asc <= -19776) return "B";
	if($asc >= -19775 and $asc <= -19219) return "C";
	if($asc >= -19218 and $asc <= -18711) return "D";
	if($asc >= -18710 and $asc <= -18527) return "E";
	if($asc >= -18526 and $asc <= -18240) return "F";
	if($asc >= -18239 and $asc <= -17923) return "G";
	if($asc >= -17922 and $asc <= -17418) return "H";
	if($asc >= -17922 and $asc <= -17418) return "I";
	if($asc >= -17417 and $asc <= -16475) return "J";
	if($asc >= -16474 and $asc <= -16213) return "K";
	if($asc >= -16212 and $asc <= -15641) return "L";
	if($asc >= -15640 and $asc <= -15166) return "M";
	if($asc >= -15165 and $asc <= -14923) return "N";
	if($asc >= -14922 and $asc <= -14915) return "O";
	if($asc >= -14914 and $asc <= -14631) return "P";
	if($asc >= -14630 and $asc <= -14150) return "Q";
	if($asc >= -14149 and $asc <= -14091) return "R";
	if($asc >= -14090 and $asc <= -13319) return "S";
	if($asc >= -13318 and $asc <= -12839) return "T";
	if($asc >= -12838 and $asc <= -12557) return "W";
	if($asc >= -12556 and $asc <= -11848) return "X";
	if($asc >= -11847 and $asc <= -11056) return "Y";
	if($asc >= -11055 and $asc <= -10247) return "Z";
	return NULL;
}