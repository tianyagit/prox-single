<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/9
 * Time: 9:59
 * 粉丝管理
 */

defined('IN_IA') or exit('Access Denied');

$dos = array('display', 'addTag', 'delTag', 'edit_tagname', 'edit_fans_tag', 'batch_edit_fans_tag');
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
	} elseif($_GPC['type'] == '') {
		$condition .= " AND f.`uid` = 0";
		$type = '';
	}
	$nickname = $_GPC['nickname'] ? addslashes(trim($_GPC['nickname'])) : '';
	if (!empty($nickname)) {
		$condition .= " AND ((f.`nickname` LIKE :nickname) OR (f.`openid` = :openid))";
		$param[':nickname'] = "%".$nickname."%";
		$param[':openid'] = $nickname;
	}
	if (!empty($_GPC['time']['start'])) {
		$starttime = strtotime($_GPC['time']['start']);
		$endtime = strtotime($_GPC['time']['end']) + 86399;
	} else {
		$starttime = strtotime('-3 month', time());
		$endtime = time();
	}
	$param[':starttime'] = $starttime;
	$param[':endtime'] = $endtime;
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
	$fans_list = pdo_fetchall("SELECT f.* FROM " .tablename('mc_mapping_fans')." AS f LEFT JOIN ".tablename('mc_fans_tag_mapping')." AS m ON m.`fanid` = f.`fanid`". $condition. " GROUP BY f.`fanid`" . $orderby . " LIMIT " .($pageindex - 1) * $pagesize.",".$pagesize, $param);
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

if ($do == 'addTag') {
	$tag = $_GPC['tag'];
	$wechat_api = WeAccount::create($_W['acid']);
	$result = $wechat_api->fansTagAdd($tag);
	if (is_error($result)) {
		message($result, '', 'ajajx');
	} else {
		message(error(0), '', 'ajax');
	}
}

if ($do == 'delTag') {
	$tagid = intval($_GPC['tag']);
	$wechat_api = WeAccount::create($_W['acid']);
	$tags = $wechat_api->fansTagDelete($tagid);
	if (!is_error($tags)) {
		$fans_list = pdo_getall('mc_mapping_fans', array('groupid LIKE' => "%,{$tagid},%"));
		$count = count($fans_list);
		if (!empty($count)) {
			$buffSize = ceil($count / 500);
			for ($i = 0; $i < $buffSize; $i++) {
				$sql = '';
				$buffer = array_slice($fans_list, $i * 500, 500);
				foreach ($buffer as $fans) {
					$tagids = trim(str_replace(','.$tagid.',', ',', $fans['groupid']), ',');
					if ($tagids == ',') {
						$tagids = '';
					}
					$sql .= 'UPDATE ' . tablename('mc_mapping_fans') . " SET `groupid`='" . $tagids . "' WHERE `fanid`={$fans['fanid']};";
				}
				pdo_query($sql); 		// 500条更新，执行一次sql请求
			}
		}
		pdo_delete('mc_fans_tag_mapping', array('tagid' => $tagid));
		message(error(0, 'success'), '', 'ajax');
	} else {
		message(error(-1, $tags['message']), '', 'ajax');
	}
}

if ($do == 'edit_tagname') {
	$tag = intval($_GPC['tag']);
	$tag_name = $_GPC['tag_name'];
	$wechat_api = WeAccount::create($_W['acid']);
	$result = $wechat_api->fansTagEdit($tag, $tag_name);
	if (is_error($result)) {
		message($result, '', 'ajax');
	} else {
		message(error('0'), '', 'ajax');
	}
}

if ($do == 'edit_fans_tag') {
	$fanid = intval($_GPC['fanid']);
	$tags = $_GPC['tags'];
	$openid = pdo_getcolumn('mc_mapping_fans', array('uniacid' => $_W['uniacid'], 'fanid' => $fanid), 'openid');
	$wechat_api = WeAccount::create($_W['acid']);
	$result = $wechat_api->fansTagTagging($openid, $tags);
	if (!is_error($result)) {
		pdo_delete('mc_fans_tag_mapping', array('fanid' => $fanid));
		if (!empty($tags)) {
			foreach ($tags as $tag) {
				pdo_insert('mc_fans_tag_mapping', array('fanid' => $fanid, 'tagid' => $tag));
			}
			$tags = implode(',', $tags);
			pdo_update('mc_mapping_fans', array('groupid' => $tags), array('fanid' => $fanid));
		}
	}
	message($result, '', 'ajax');
}

if ($do == 'batch_edit_fans_tag') {
	$openid_list = $_GPC['openid'];
	$tags = $_GPC['tag'];
	$wechat_api = WeAccount::create($_W['acid']);
	foreach ($tags as $tag) {
		$result = $wechat_api->fansTagBatchTagging($openid_list, $tags[0]);
		if (!is_error($result)) {
			foreach ($openid_list as $openid) {
				$fan_info = pdo_get('mc_mapping_fans', array('uniacid' => $_W['uniacid'], 'openid' => $openid));
				pdo_insert('mc_fans_tag_mapping', array('fanid' => $fan_info['fanid'], 'tagid' => $tag));
				$groupid = $fan_info['group'].",".$tag;
				pdo_update('mc_mapping_fans', array('groupid' => $groupid), array('uniacid' => $_W['uniacid'], 'openid' => $openid));
			}
		} else {
			message($result, '', 'ajax');
		}
	}
	message(error(0), '', 'ajax');
}
template('mc/fans');

