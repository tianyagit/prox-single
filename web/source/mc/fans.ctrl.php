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
	$pageindex = max(1, intval($_GPC['page']));
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
//	if (!empty($_GPC['time']['start'])) {
//		$starttime = strtotime($_GPC['time']['start']);
//		$endtime = strtotime($_GPC['time']['end']) + 86399;
//	} else {
//		$starttime = strtotime('-3 month', time());
//		$endtime = time();
//	}
//	$param[':starttime'] = $starttime;
//	$param[':endtime'] = $endtime;

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
	$fans_list = pdo_fetchall("SELECT a.* FROM (SELECT f.* FROM " .tablename('mc_mapping_fans')." AS f LEFT JOIN ".tablename('mc_fans_tag_mapping')." AS m ON m.`fanid` = f.`fanid`". $condition. " GROUP BY f.`fanid`" . $orderby . " LIMIT " .($pageindex - 1) * $pagesize.",".$pagesize. " ) as a RIGHT JOIN ". tablename('mc_members') ." AS b ON a.uid = b.uid WHERE b.mobile <> ''", $param);
	if (!empty($fans_list)) {
		foreach ($fans_list as &$v) {
			$v['tag_show'] = mc_show_tag($v['groupid']);
			$v['groupid'] = trim($v['groupid'], ',');
			if (!empty($v['uid'])) {
				$user = mc_fetch($v['uid'], array('realname', 'nickname', 'mobile', 'email', 'avatar'));
			}
			if (!empty($v['tag']) && is_string($v['tag'])) {
				if (is_base64($v['tag'])) {
					$v['tag'] = base64_decode($v['tag']);
				}
				// report warning
				if (is_serialized($v['tag'])) {
					$v['tag'] = @iunserializer($v['tag']);
				}
				if (!empty($v['tag']['headimgurl'])) {
					$v['tag']['avatar'] = tomedia($v['tag']['headimgurl']);
				}
			}
			if (empty($v['tag'])) {
				$v['tag'] = array();
			}

			if (!empty($user)) {
				$niemmo = $user['realname'];
				if (empty($niemmo)) {
					$niemmo = $user['nickname'];
				}
				if (empty($niemmo)) {
					$niemmo = $user['mobile'];
				}
				if (empty($niemmo)) {
					$niemmo = $user['email'];
				}
				if (empty($niemmo) || (!empty($niemmo) && substr($niemmo, -6) == 'we7.cc' && strlen($niemmo) == 39)) {
					$niemmo_effective = 0;
				} else {
					$niemmo_effective = 1;
				}
				$v['user'] = array('niemmo_effective' => $niemmo_effective, 'niemmo' => $niemmo, 'nickname' => $user['nickname']);
			}
			if (empty($v['user']['nickname']) && !empty($v['tag']['nickname'])) {
				$v['user']['nickname'] = $v['tag']['nickname'];
			}
			if (empty($v['user']['avatar']) && !empty($v['tag']['avatar'])) {
				$v['user']['avatar'] = $v['tag']['avatar'];
			}
			unset($user,$niemmo,$niemmo_effective);
		}
	}
	$total = pdo_fetchcolumn("SELECT COUNT(DISTINCT f.`fanid`) FROM " .tablename('mc_mapping_fans')." AS f LEFT JOIN ".tablename('mc_fans_tag_mapping').' AS m ON m.`fanid` = f.`fanid`'.$condition, $param);
	$pager = pagination($total, $pageindex, $pagesize);
	$fans['total'] = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_mapping_fans') . ' WHERE uniacid = :uniacid AND acid = :acid AND follow = 1', array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid']));
}
template('mc/fans');

