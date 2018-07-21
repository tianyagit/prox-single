<?php
defined('IN_IA') or exit('Access Denied');

class XzappAccount extends WeAccount {
	public $tablename = 'account_xzapp';

	public $apis = array(
		'perm' => array(
			'add' => 'https://openapi.baidu.com/rest/2.0/cambrian/media/add_material',
		),
	);

	public function __construct($account = array()) {
		$this->menuFrame = 'account';
		$this->type = ACCOUNT_TYPE_XZAPP_NORMAL;
		$this->typeName = '熊掌号';
		$this->typeSign = XZAPP_TYPE_SIGN;
		$this->typeTempalte = '-xzapp';
	}

	public function checkSign() {
		$arrParams = array(
			$token = $this->account['token'],
			$intTimeStamp = $_GET['timestamp'],
			$strNonce = $_GET['nonce'],
		);
		sort($arrParams, SORT_STRING);
		$strParam = implode($arrParams);
		$strSignature = sha1($strParam);
		return $strSignature == $_GET['signature'];
	}

	public function getAccessToken() {
		$cachekey = cache_system_key('accesstoken', array('acid' => $this->account['acid']));
		$cache = cache_load($cachekey);

		if (!empty($cache) && !empty($cache['token']) && $cache['expire'] > TIMESTAMP) {
			$this->account['access_token'] = $cache;
			return $cache['token'];
		}

		if (empty($this->account['key']) || empty($this->account['secret'])) {
			return error('-1', '未填写熊掌号的 appid 或者 appsecret！');
		}

		$url = "https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id={$this->account['key']}&client_secret={$this->account['secret']}";
		$content = ihttp_get($url);
		$token = @json_decode($content['content'], true);

		$record = array();
		$record['token'] = $token['access_token'];
		$record['expire'] = TIMESTAMP + $token['expires_in'] - 200;
		$this->account['access_token'] = $record;

		cache_write($cachekey, $record);
		return $record['token'];
	}

	protected function requestApi($url, $post = '') {
		$response = ihttp_request($url, $post);
		$result = @json_decode($response['content'], true);
		if ($result['error_code']) {
			return error(-1, "访问熊掌号接口失败, 错误代码：【{$result['error_code']}】, 错误信息：【{$result['error_msg']}】");
		}
		return $result;
	}

	public function checkIntoManage() {
		if (empty($this->account) || (!empty($this->uniaccount['account']) && $this->uniaccount['type'] != ACCOUNT_TYPE_XZAPP_NORMAL && !defined('IN_MODULE'))) {
			return false;
		}
		return true;
	}

	public function fetchAccountInfo() {
		$account_table = table('account_xzapp');
		$account = $account_table->getXzappAccount($this->uniaccount['acid']);
		return $account;
	}

	public function accountDisplayUrl() {
		return url('account/display', array('type' => XZAPP_TYPE_SIGN));
	}

	public function isTagSupported() {
		if (!empty($this->account['key']) && !empty($this->account['secret'])) {
			return true;
		} else {
			return false;
		}
	}

	public function fansTagFetchAll() {
		$token = $this->getAccessToken();

		if (is_error($token)) {
			return $token;
		}

		$url = "https://openapi.baidu.com/rest/2.0/cambrian/tags/get?access_token={$token}";
		$result = $this->requestApi($url);
		return $result;
	}

	public function fansAll($startopenid = '') {
		global $_W;
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}

		$url = "https://openapi.baidu.com/rest/2.0/cambrian/user/get?start_index=0&access_token={$token}";
		if (!empty($_GPC['next_openid'])) {
			$url .= '&start_index=' . $_GPC['next_openid'];
		}

		$res = ihttp_get($url);
		$content = json_decode($res['content'], true);

		if ($content['error_code']) {
			return error(-1, '访问熊掌号接口失败, 错误代码: 【' . $content['error_code'] . '】, 错误信息：【' . $content['error_msg'] . '】');
		}

		$return = array();
		$return['total'] = $content['total'];
		$return['fans'] = $content['data'];
		$return['next'] = $content['start_index'];
		return $return;
	}

	/**
	 * 获取用户基本信息(单个)
	 * @param $uniid
	 * @param bool $isOpen
	 * @return array
	 */
	public function fansQueryInfo($uniid, $isOpen = true) {
		if ($isOpen) {
			$openid = $uniid;
		} else {
			exit('error');
		}
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$data = array(
			'user_list' => array(
				array(
					'openid' => $uniid,
				)
			),
		);
		$url = "https://openapi.baidu.com/rest/2.0/cambrian/user/info?access_token={$token}";

		$result = $this->requestApi($url, json_encode($data));
		return $result['user_info_list'][0];
	}

	/**
	 * 获取用户基本信息(批量)
	 * @param $data
	 * @return array
	 */
	public function fansBatchQueryInfo($data) {
		if (empty($data)) {
			return error(-1, '粉丝 openid 错误');
		}

		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}

		$list['user_list'] = array();
		foreach ($data as $da) {
			$list['user_list'][] = array('openid' => $da);
		}

		$url = "https://openapi.baidu.com/rest/2.0/cambrian/user/info?access_token={$token}";
		$result = $this->requestApi($url, json_encode($list));
		return $result['user_info_list'];
	}

	/**
	 * 创建粉丝标签
	 * @param 	array 		$tagname
	 * @return 	array 		$result
	 *
	 */
	public function fansTagAdd($tagname) {
		if(empty($tagname)) {
			return error(-1, '请填写标签名称');
		}
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}

		$url = "https://openapi.baidu.com/rest/2.0/cambrian/tags/create?access_token={$token}";
		$data = stripslashes(ijson_encode(array('tag' => array('name' => $tagname)), JSON_UNESCAPED_UNICODE));
		$result = $this->requestApi($url, $data);
		return $result;
	}

	/**
	 * 单个粉丝打标签
	 * @param $openid
	 * @param $tagids
	 * @return array|bool|mixed
	 */
	public function fansTagTagging($openid, $tagids) {
		$openid = (string) $openid;
		$tagids = (array) $tagids;

		if (empty($openid)) {
			return error(-1, '没有填写用户openid');
		}
		if (empty($tagids)) {
			return error(-1, '没有填写标签');
		}
		if (count($tagids) > 3) {
			return error(-1, '最多3个标签');
		}
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}

		//删除粉丝之前的标签
		$fetch_result = $this->fansTagFetchOwnTags($openid);
		if (is_error($fetch_result)) {
			return $fetch_result;
		}
		foreach ($fetch_result['tagid_list'] as $del_tagid) {
			$this->fansTagBatchUntagging($openid, $del_tagid);
		}

		$url = "https://openapi.baidu.com/rest/2.0/cambrian/tags/batchtagging?access_token={$token}";
		foreach ($tagids as $tagid) {
			$data = array(
				'openid_list' => array($openid),
				'tagid' => $tagid
			);
			$data = json_encode($data);
			$result = $this->requestApi($url, $data);
			if (is_error($result)) {
				return $result;
			}
		}
		return true;
	}

	/**
	 * 获取用户身上的标签列表
	 * @param $openid
	 * @return array|mixed
	 */
	public function fansTagFetchOwnTags($openid) {
		$openid = (string)$openid;
		if (empty($openid)) {
			return error(-1, '没有填写用户openid');
		}
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}

		$url = "https://openapi.baidu.com/rest/2.0/cambrian/tags/getidlist?access_token={$token}";
		$data = json_encode(array('openid' => $openid));
		$result = $this->requestApi($url, $data);
		return $result;
	}

	/**
	 * 批量为用户取消标签
	 * @param $openid_list
	 * @param $tagid
	 * @return array|bool|mixed
	 */
	public function fansTagBatchUntagging($openid_list, $tagid) {
		$openid_list = (array)$openid_list;
		$tagid = (int)$tagid;
		if (empty($openid_list)) {
			return error(-1, '缺少openid参数');
		}
		if (empty($tagid)) {
			return error(-1, '没有填写tagid');
		}

		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://openapi.baidu.com/rest/2.0/cambrian/tags/batchuntagging?access_token={$token}";
		$data = array(
			'openid_list' => $openid_list,
			'tagid' => $tagid
		);
		$data = json_encode($data);
		$result = $this->requestApi($url, $data);
		if (is_error($result)) {
			return $result;
		}
		return true;
	}

	/**
	 * 批量为用户打标签
	 * @param $openid_list
	 * @param $tagid
	 * @return array|bool|mixed
	 */
	public function fansTagBatchTagging($openid_list, $tagid) {
		$openid_list = (array)$openid_list;
		$tagid = (int)$tagid;
		if(empty($openid_list)){
			return error(-1, '没有填写用户openid列表');
		}
		if(empty($tagid)) {
			return error(-1, '没有填写tagid');
		}
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://openapi.baidu.com/rest/2.0/cambrian/tags/batchtagging?access_token={$token}";
		$data = array(
			'openid_list' => $openid_list,
			'tagid' => $tagid
		);
		$result = $this->requestApi($url, json_encode($data));
		if (is_error($result)) {
			return $result;
		}
		return true;
	}

	# 自定义菜单
	/**
	 * API配置自定义菜单查询
	 * @return array|mixed
	 */
	public function menuCurrentQuery() {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://openapi.baidu.com/rest/2.0/cambrian/menu/get?access_token={$token}";
		$res = $this->requestApi($url);
		return $res;
	}

	/**
	 * 自定义菜单创建
	 * @param $menu
	 * @return array|mixed
	 */
	public function menuCreate($menu) {
		global $_W;
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$data['menues'] = json_encode($menu);
		$url = "https://openapi.baidu.com/rest/2.0/cambrian/menu/create?access_token={$token}";
		$res = $this->requestApi($url, $data);
		if (is_error($res)) {
			return $res;
		} else {
			return 0;
		}
	}


	# 素材
	/**
	 * 获取熊掌号素材列表（熊掌号只支持图片和图文）
	 * @param string $type	素材的类型:image/news
	 * @param int $offset	素材偏移的位置，从０开始
	 * @param int $count	素材的数量，取值在１－２０之间，默认２０
	 * @return array|mixed
	 */
	public function batchGetMaterial($type = 'news', $offset = 0, $count = 20) {
		global $_W;
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://openapi.baidu.com/rest/2.0/cambrian/material/batchget_material?access_token={$token}&type={$type}&offset={$offset}&count={$count}";
		$response = ihttp_get($url);
		$content = @json_decode($response['content'], true);

		if ($content['error_code']) {
			return error(-1, "访问熊掌号接口失败, 错误代码：【{$content['error_code']}】, 错误信息：【{$content['error_msg']}】");
		}
		return $content;
	}

	/**
	 * 删除永久素材
	 * @param $media_id
	 * @return array|mixed
	 */
	public function delMaterial($media_id) {
		$media_id = trim($media_id);
		if (empty($media_id)) {
			return error(-1, '素材media_id错误');
		}
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://openapi.baidu.com/rest/2.0/cambrian/material/del_material?access_token=" . $token . "&media_id=" . $media_id;

		$response = $this->requestApi($url);
		return $response;
	}

	/**
	 * 新增永久图文素材
	 * @param $data
	 * @return array
	 */
	public function addMatrialNews($data) {
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://openapi.baidu.com/rest/2.0/cambrian/material/add_news?access_token={$token}";
		$data = stripslashes(urldecode(ijson_encode($data, JSON_UNESCAPED_UNICODE)));

		$response = $this->requestApi($url, $data);
		return $response['media_id'];
	}

	/**
	 * 修改永久图文素材
	 * @param $data
	 * @return array|mixed
	 */
	public function editMaterialNews($data) {
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://openapi.baidu.com/rest/2.0/cambrian/material/update_news?access_token={$token}";

		$response = $this->requestApi($url, stripslashes(ijson_encode($data, JSON_UNESCAPED_UNICODE)));
		return $response;
	}

	/**
	 * 获取永久素材
	 * @param $media_id
	 * @return array|mixed
	 */
	public function getMaterial($media_id) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}

		$url = "https://openapi.baidu.com/rest/2.0/cambrian/material/get_material?access_token={$token}&media_id={$media_id}";

		$response = $this->requestApi($url);
		return $response;
	}

	/**
	 * 上传图文消息内的图片获取URL
	 * @param $thumb
	 * @return array
	 */
	public function uploadNewsThumb($thumb) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		if (!file_exists($thumb)) {
			return error(1, '文件不存在');
		}

		$data = array(
			'media' => '@' . $thumb,
		);

		$url = "https://openapi.baidu.com/rest/2.0/cambrian/media/uploadimg?access_token={$token}";

		$response = $this->requestApi($url, $data);
		return $response['url'];
	}

	public function uploadMediaFixed() {
		# 未测试
		if (empty($path)) {
			return error(-1, '参数错误');
		}
		if (in_array(substr(ltrim($path, '/'), 0, 6), array('images', 'videos', 'audios', 'thumb'))) {
			$path = ATTACHMENT_ROOT . ltrim($path, '/');
		}
		if (!file_exists($path)) {
			return error(1, '文件不存在');
		}
		$token = $this->getAccessToken();
		if (is_error($token)){
			return $token;
		}
		$data = array(
			'media' => '@' . $path
		);
		$url = $this->apis['perm']['add'] . "?access_token={$token}";

		$response = $this->requestApi($url, $data);
		return $response;
	}

}