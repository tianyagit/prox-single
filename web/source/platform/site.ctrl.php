<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/source/platform/url2qr.ctrl.php : 2016年11月5日 10:40:19 brjun $
 */

defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('platform_site');
$dos = array('wesite', 'article', 'wesite_tpl');
$do = !empty($_GPC['do']) && in_array($do, $dos) ? $do : 'wesite';

if($do == 'wesite') {

	template('platform/wesite-display');
}

if($do == 'wesite_tpl') {

	template('platform/wesite-tpl-display');
}

if($do == 'article') {
	$operations = array('edit_article', 'del_article', 'edit_category', 'del_category', 'display_category');
	$operation = !empty($_GPC['operation']) && in_array($_GPC['operation'], $operations) ? $_GPC['operation'] : '';
	switch ($operation) {
		case 'edit_article':
			load()->func('file');
			$id = intval($_GPC['id']);
			$category = pdo_fetchall("SELECT id,parentid,name FROM ".tablename('site_category')." WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');
			$parent = array();
			$children = array();
			if (!empty($category)) {
				foreach ($category as $cid => $cate) {
					if (!empty($cate['parentid'])) {
						$children[$cate['parentid']][] = $cate;
					} else {
						$parent[$cate['id']] = $cate;
					}
				}
			}
			//微站风格模板
			$template = uni_templates();
			$pcate = intval($_GPC['pcate']);
			$ccate = intval($_GPC['ccate']);
			if (!empty($id)) {
				$item = pdo_fetch("SELECT * FROM ".tablename('site_article')." WHERE id = :id" , array(':id' => $id));
				$item['type'] = explode(',', $item['type']);
				$pcate = $item['pcate'];
				$ccate = $item['ccate'];
				if (empty($item)) {
					message('抱歉，文章不存在或是已经删除！', '', 'error');
				}
				$key = pdo_fetchall('SELECT content FROM ' . tablename('rule_keyword') . ' WHERE rid = :rid AND uniacid = :uniacid', array(':rid' => $item['rid'], ':uniacid' => $_W['uniacid']));
				if(!empty($key)) {
					$keywords = array();
					foreach($key as $row) {
						$keywords[] = $row['content'];
					}
					$keywords = implode(',', array_values($keywords));
				}
				$item['credit'] = iunserializer($item['credit']) ? iunserializer($item['credit']) : array();
				if(!empty($item['credit']['limit'])) {
					//获取该文章已经赠送的积分数
					$credit_num = pdo_fetchcolumn('SELECT SUM(credit_value) FROM ' . tablename('mc_handsel') . ' WHERE uniacid = :uniacid AND module = :module AND sign = :sign', array(':uniacid' => $_W['uniacid'], ':module' => 'article', ':sign' => md5(iserializer(array('id' => $id)))));
					if(is_null($credit_num)) $credit_num = 0;
					$credit_yu = (($item['credit']['limit'] - $credit_num) < 0) ? 0 : $item['credit']['limit'] - $credit_num;
				}
			} else {
				$item['credit'] = array();
				$keywords = '';
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['title'])) {
					message('标题不能为空，请输入标题！');
				}
				$data = array(
					'uniacid' => $_W['uniacid'],
					'iscommend' => intval($_GPC['option']['commend']),
					'ishot' => intval($_GPC['option']['hot']),
					'pcate' => intval($_GPC['category']['parentid']),
					'ccate' => intval($_GPC['category']['childid']),
					'template' => addslashes($_GPC['template']),
					'title' => addslashes($_GPC['title']),
					'description' => addslashes($_GPC['description']),
					'content' => htmlspecialchars_decode($_GPC['content'], ENT_QUOTES),
					'incontent' => intval($_GPC['incontent']),
					'source' => addslashes($_GPC['source']),
					'author' => addslashes($_GPC['author']),
					'displayorder' => intval($_GPC['displayorder']),
					'linkurl' => addslashes($_GPC['linkurl']),
					'createtime' => TIMESTAMP,
					'click' => intval($_GPC['click'])
				);
				if (!empty($_GPC['thumb'])) {
					$data['thumb'] = $_GPC['thumb'];
				} elseif (!empty($_GPC['autolitpic'])) {
					$match = array();
					$file_name = file_random_name(ATTACHMENT_ROOT.'images/'.$_W['uniacid'].'/'.date('Y/m').'/', 'jpg');
					$path = 'images/'.$_W['uniacid'].'/'.date('Y/m').'/'.$file_name;
					preg_match('/&lt;img.*?src=&quot;(.*?)&quot;/', $_GPC['content'], $match);
					if (!empty($match[1])) {
						$url = $match[1];
						$file = file_get_contents($url);
						file_write($path, $file);
						$data['thumb'] = $path;
						file_remote_upload($path);
					}
				} else {
					$data['thumb'] = '';
				}
				$keyword = str_replace('，', ',', trim($_GPC['keyword']));
				$keyword = explode(',', $keyword);
				if(!empty($keyword)) {
					$rule['uniacid'] = $_W['uniacid'];
					$rule['name'] = '文章：' . $_GPC['title'] . ' 触发规则';
					$rule['module'] = 'news';
					$rule['status'] = 1;
					$keywords = array();
					foreach($keyword as $key) {
						$key = trim($key);
						if(empty($key)) continue;
						$keywords[] = array(
							'uniacid' => $_W['uniacid'],
							'module' => 'news',
							'content' => $key,
							'status' => 1,
							'type' => 1,
							'displayorder' => 1,
						);
					}
					$reply['title'] = $_GPC['title'];
					$reply['description'] = $_GPC['description'];
					$reply['thumb'] = $data['thumb'];
					$reply['url'] = murl('site/site/detail', array('id' => $id));
				}
				//积分设置
				if(!empty($_GPC['credit']['status'])) {
					$credit['status'] = intval($_GPC['credit']['status']);
					$credit['limit'] = intval($_GPC['credit']['limit']) ? intval($_GPC['credit']['limit']) : message('请设置积分上限');
					$credit['share'] = intval($_GPC['credit']['share']) ? intval($_GPC['credit']['share']) : message('请设置分享时赠送积分多少');
					$credit['click'] = intval($_GPC['credit']['click']) ? intval($_GPC['credit']['click']) : message('请设置阅读时赠送积分多少');
					$data['credit'] = iserializer($credit);
				} else {
					$data['credit'] = iserializer(array('status' => 0, 'limit' => 0, 'share' => 0, 'click' => 0));
				}	
				if (empty($id)) {
					if(!empty($keywords)) {
						pdo_insert('rule', $rule);
						$rid = pdo_insertid();
						foreach($keywords as $li) {
							$li['rid'] = $rid;
							pdo_insert('rule_keyword', $li);
						}
						$reply['rid'] = $rid;
						pdo_insert('news_reply', $reply);
						$data['rid'] = $rid;
					}
					pdo_insert('site_article', $data);
					$aid = pdo_insertid();
					pdo_update('news_reply', array('url' => murl('site/site/detail', array('id' => $aid))), array('rid' => $rid));
				} else {
					unset($data['createtime']);
					pdo_delete('rule', array('id' => $item['rid'], 'uniacid' => $_W['uniacid']));
					pdo_delete('rule_keyword', array('rid' => $item['rid'], 'uniacid' => $_W['uniacid']));
					pdo_delete('news_reply', array('rid' => $item['rid']));
					if(!empty($keywords)) {
						pdo_insert('rule', $rule);
						$rid = pdo_insertid();

						foreach($keywords as $li) {
							$li['rid'] = $rid;
							pdo_insert('rule_keyword', $li);
						}

						$reply['rid'] = $rid;
						pdo_insert('news_reply', $reply);
						$data['rid'] = $rid;
					} else {
						$data['rid'] = 0;
						$data['kid'] = 0;
					}
					pdo_update('site_article', $data, array('id' => $id));
				}
				message('文章更新成功！', url('platform/site/article'), 'success');
			} else {
				template('platform/wesite-article-post');
			}
			break;
		case 'del_article':
			load()->func('file');
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id,rid,kid,thumb FROM ".tablename('site_article')." WHERE id = :id", array(':id' => $id));
			
			if (empty($row)) {
				message('抱歉，文章不存在或是已经被删除！');
			}
			if (!empty($row['thumb'])) {
				file_delete($row['thumb']);
			}
			if(!empty($row['rid'])) {
				pdo_delete('rule', array('id' => $row['rid'], 'uniacid' => $_W['uniacid']));
				pdo_delete('rule_keyword', array('rid' => $row['rid'], 'uniacid' => $_W['uniacid']));
				pdo_delete('news_reply', array('rid' => $row['rid']));
			}
			if(pdo_delete('site_article', array('id' => $id))){
				message('删除成功！', referer(), 'success');
			}else {
				message('删除失败！', referer(), 'error');
			}
			break;
		case 'edit_category':
			$parentid = intval($_GPC['parentid']);
			$id = intval($_GPC['id']);
			//获取当前默认微站的模板
			$setting = uni_setting($_W['uniacid'], array('default_site'));
			$site_styleid = pdo_fetchcolumn('SELECT styleid FROM ' . tablename('site_multi') . ' WHERE id = :id', array(':id' => $setting['default_site']));
			if($site_styleid) {
				$site_template = pdo_fetch("SELECT a.*,b.name,b.sections FROM ".tablename('site_styles').' AS a LEFT JOIN ' . tablename('site_templates') . ' AS b ON a.templateid = b.id WHERE a.uniacid = :uniacid AND a.id = :id', array(':uniacid' => $_W['uniacid'], ':id' => $site_styleid));
			}

			//微站风格模板
			$styles = pdo_fetchall("SELECT a.*, b.name AS tname, b.title FROM ".tablename('site_styles').' AS a LEFT JOIN ' . tablename('site_templates') . ' AS b ON a.templateid = b.id WHERE a.uniacid = :uniacid', array(':uniacid' => $_W['uniacid']), 'id');
			if(!empty($id)) {
				$category = pdo_fetch("SELECT * FROM ".tablename('site_category')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
				if(empty($category)) {
					message('分类不存在或已删除', '', 'error');
				}
				if (!empty($category['css'])) {
					$category['css'] = iunserializer($category['css']);
				} else {
					$category['css'] = array();
				}
			} else {
				$category = array(
					'displayorder' => 0,
					'css' => array(),
				);
			}
			if (!empty($parentid)) {
				$parent = pdo_fetch("SELECT id, name FROM ".tablename('site_category')." WHERE id = '$parentid'");
				if (empty($parent)) {
					message('抱歉，上级分类不存在或是已经被删除！', url('site/category/display'), 'error');
				}
			}
			$category['style'] = $styles[$category['styleid']];
			$category['style']['tname'] = empty($category['style']['tname'])? 'default' : $category['style']['tname'];
			if(!empty($category['nid'])) {
				$category['nav'] = pdo_get('site_nav', array('id' => $category['nid']));
			} else {
				$category['nav'] = array();
			}
			$multis = pdo_getall('site_multi', array('uniacid' => $_W['uniacid']), array(), 'id');

			if (checksubmit('submit')) {
				if (empty($_GPC['cname'])) {
					message('抱歉，请输入分类名称！');
				}
				$data = array(
					'uniacid' => $_W['uniacid'],
					'name' => $_GPC['cname'],
					'displayorder' => intval($_GPC['displayorder']),
					'parentid' => intval($parentid),
					'description' => $_GPC['description'],
					'styleid' => intval($_GPC['styleid']),
					'linkurl' => $_GPC['linkurl'],
					'ishomepage' => intval($_GPC['ishomepage']),
					'enabled' => intval($_GPC['enabled']),
					'icontype' => intval($_GPC['icontype']),
					'multiid' => intval($_GPC['multiid'])
				);
				
				if($data['icontype'] == 1) {
					$data['icon'] = '';
					$data['css'] = serialize(array(
						'icon' => array(
							'font-size' => $_GPC['icon']['size'],
							'color' => $_GPC['icon']['color'],
							'width' => $_GPC['icon']['size'],
							'icon' => empty($_GPC['icon']['icon']) ? 'fa fa-external-link' : $_GPC['icon']['icon'],
						),
					));
				} else {
					$data['css'] = '';
					$data['icon'] = $_GPC['iconfile'];
				}
				
				$isnav = intval($_GPC['isnav']);
				if($isnav) {
					$nav = array(
						'uniacid' => $_W['uniacid'],
						'categoryid' => $id,
						'displayorder' => $_GPC['displayorder'],
						'name' => $_GPC['cname'],
						'description' => $_GPC['description'],
						'url' => "./index.php?c=site&a=site&cid={$category['id']}&i={$_W['uniacid']}",
						'status' => 1,
						'position' => 1,
						'multiid' => intval($_GPC['multiid']),
					);
					if ($data['icontype'] == 1) {
						$nav['icon'] = '';
						$nav['css'] = serialize(array(
							'icon' => array(
								'font-size' => $_GPC['icon']['size'],
								'color' => $_GPC['icon']['color'],
								'width' => $_GPC['icon']['size'],
								'icon' => empty($_GPC['icon']['icon']) ? 'fa fa-external-link' : $_GPC['icon']['icon'],
							),
							'name' => array(
								'color' => $_GPC['icon']['color'],
							),
						));
					} else {
						$nav['css'] = '';
						$nav['icon'] = $_GPC['iconfile'];
					}
					if($category['nid']) {
						$nav_exist = pdo_fetch('SELECT id FROM ' . tablename('site_nav') . ' WHERE id = :id AND uniacid = :uniacid', array(':id' => $category['nid'], ':uniacid' => $_W['uniacid']));
					} else {
						$nav_exist = '';
					}
					if(!empty($nav_exist)) {
						pdo_update('site_nav', $nav, array('id' => $category['nid'], 'uniacid' => $_W['uniacid']));
					} else {
						pdo_insert('site_nav', $nav);
						$nid = pdo_insertid();
						$data['nid'] = $nid;
					}
				} else {
					if($category['nid']) {
						$data['nid'] = 0;
						pdo_delete('site_nav', array('id' => $category['nid'], 'uniacid' => $_W['uniacid']));
					}
				}
				if (!empty($id)) {
					unset($data['parentid']);
					pdo_update('site_category', $data, array('id' => $id));
				} else {
					pdo_insert('site_category', $data);
					$id = pdo_insertid();
					$nav_url['url'] = "./index.php?c=site&a=site&cid={$id}&i={$_W['uniacid']}";
					pdo_update('site_nav', $nav_url, array('id' => $data['nid'], 'uniacid' => $_W['uniacid']));
				}
				message('更新分类成功！', url('platform/site/article', array('operation' => 'display_category')), 'success');
			}
			template('platform/wesite-category-post');
			break;
		case 'del_category':
			load()->func('file');
			$id = intval($_GPC['id']);
			$category = pdo_fetch("SELECT id, parentid, nid FROM ".tablename('site_category')." WHERE id = '$id'");
			if (empty($category)) {
				message('抱歉，分类不存在或是已经被删除！', referer(), 'error');
			}
			$navs = pdo_fetchall("SELECT icon, id FROM ".tablename('site_nav')." WHERE id IN (SELECT nid FROM ".tablename('site_category')." WHERE id = {$id} OR parentid = '$id')", array(), 'id');
			if (!empty($navs)) {
				foreach ($navs as $row) {
					file_delete($row['icon']);
				}
				pdo_query("DELETE FROM ".tablename('site_nav')." WHERE id IN (".implode(',', array_keys($navs)).")");
			}
			pdo_delete('site_category', array('id' => $id, 'parentid' => $id), 'OR');
			message('分类删除成功！', referer(), 'success');
			break;
		case 'display_category':
			if (!empty($_GPC['displayorder'])) {
				foreach ($_GPC['displayorder'] as $id => $displayorder) {
					$update = array('displayorder' => intval($displayorder['displayorder']));
					pdo_update('site_category', $update, array('id' => $id));
				}
				message('分类排序更新成功！', 'refresh', 'success');
			}
			$children = array();
			$category = pdo_fetchall("SELECT * FROM ".tablename('site_category')." WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid, displayorder DESC, id");
			foreach ($category as $index => $row) {
				if (!empty($row['parentid'])){
					$children[$row['parentid']][] = $row;
					unset($category[$index]);
				}
			}
			template('platform/wesite-category-display');
			break;
		default:
			$category = pdo_fetchall("SELECT id,parentid,name FROM ".tablename('site_category')." WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');
			$parent = array();
			$children = array();
			if (!empty($category)) {
				foreach ($category as $cid => $cate) {
					if (!empty($cate['parentid'])) {
						$children[$cate['parentid']][] = $cate;
					} else {
						$parent[$cate['id']] = $cate;
					}
				}
			}

			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$condition = '';
			$params = array();
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND `title` LIKE :keyword";
				$params[':keyword'] = "%{$_GPC['keyword']}%";
			}
			
			if (!empty($_GPC['category']['childid'])) {
				$cid = intval($_GPC['category']['childid']);
				$condition .= " AND ccate = '{$cid}'";
			} elseif (!empty($_GPC['category']['parentid'])) {
				$cid = intval($_GPC['category']['parentid']);
				$condition .= " AND pcate = '{$cid}'";
			}
			$list = pdo_fetchall("SELECT * FROM ".tablename('site_article')." WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('site_article') . " WHERE uniacid = '{$_W['uniacid']}'".$condition, $params);
			$pager = pagination($total, $pindex, $psize);
			template('platform/wesite-article-display');			
			break;
	}

}