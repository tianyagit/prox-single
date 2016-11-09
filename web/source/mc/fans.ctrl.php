<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/9
 * Time: 9:59
 * 粉丝管理
 */

defined('IN_IA') or exit('Access Denied');

$dos = array('display');
$do = in_array($do, $dos) ? $do : 'display';

load()->model('mc');
uni_user_permission_check('mc_fans');

if ($do == 'display') {
	$_W['page']['title'] = '粉丝列表';

	$fans_tag = mc_fans_groups(true);
	$pageindex = intval($_GPC['page']);
	$pagesize = 50;
	$param = array(
		':uniacid' => $_W['uniacid'],
		':acid' => $_W['acid']
		);
	$condition = " WHERE f.`uniacid` = :uniacid AND f.`acid` = :acid";
	$tag = $_GPC['tag'] ? $_GPC['tag'] : 0;
	if (!empty($tag)) {
		$param[':tagid'] = $tag;
		$condition .= " AND m.`tagid` = :tagid";
	}
	if ($_GPC['type'] == 'bind') {
		$condition .= " AND f.`uid` > 0";
		$type = 'bind';
	} elseif($_GPC['type'] == 'unbind') {
		$condition .= " AND f.`uid` = 0";
		$type = 'unbind';
	}
	$nickname = $_GPC['nickname'] ? addslashes(trim($_GPC['nickname'])) : '';
	if (!empty($nickname)) {
		$condition .= " AND ((f.`nickname` LIKE :nickname) OR (f.`openid` = :openid))";
		$param[':nickname'] = "%".$nickname."%";
		$param[':openid'] = $nickname;
	}
	if (!empty($_GPC['uid'])) {
		$condition .= " AND f.uid = :uid ";
		$param[':uid'] = intval($_GPC['uid']);
	}
	if (!empty($_GPC['time']['start'])) {
		$starttime = strtotime($_GPC['time']['start']);
		$endtime = strtotime($_GPC['time']['end']) + 86399;
		$param[':starttime'] = $starttime;
		$param[':endtime'] = $endtime;
	}
	$follow = intval($_GPC['follow']) ? intval($_GPC['follow']) : 1;
	if ($follow == 1) {
		$orderby = " ORDER BY f.`followtime` DESC";
		$condition .= " AND f.`follow` = 1";
		if (!empty($starttime)) {
			$condition .= " AND f.`followtime` >= :starttime AND f.`followtime` <= :endtime";
		}
	} elseif ($follow == 2) {
		$orderby = " ORDER BY f.`unfollowtime` DESC";
		$condition .= " AND f.`follow` = 0";
		if (!empty($starttime)) {
			$condition .= " AND f.`followtime` >= :starttime AND f.`followtime` <= :endtime";
		}
	}
	$fans_list = pdo_fetchall("SELECT f.* FROM " .tablename('mc_mapping_fans')." AS f LEFT JOIN ".tablename('mc_fans_tag_mapping')." AS m ON m.`fanid` = f.`fanid`". $condition. " GROUP BY f.`fanid`" . $orderby . " LIMIT " .($pagesize - 1) * $pagesize.",".$pagesize, $param);
	print_r($fans_list);die;
}

