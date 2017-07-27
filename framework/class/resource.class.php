<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/20
 * Time: 13:23
 */
abstract class Resource
{

	protected $uniacid = 0;
	protected $currentPage = 1;
	public function __construct() {
		global $_W;
		$this->uniacid = $_W['uniacid'];
		$this->currentPage = $this->query('page',1);
	}

	/**
	 *  获取 参数
	 * @param $key
	 * @return mixed
	 */
	protected function query($key, $default = null) {
		global $_GPC;
		return $_GPC[$key] ? $_GPC[$key] : $default;
	}

	/**
	 *  数据库路径转为绝对路径
	 * @param $path
	 */
	protected function localPath($path) {

	}

	/**
	 *  是否获取本地资源
	 * @return bool
	 */
	protected function isLocal()
	{
		return $this->query('local') == 'local';
	}
	/**
	 *  获取当前页
	 * @return int|mixed
	 */
	protected function getCurrentPage() {

		return $this->currentPage;
	}

	protected function getResourceId() {
		return $this->query('resourceid',0);//获取资源ID
	}

	public abstract function getResources();

	//转为微信资源
	public function toWx(){

	}

	//转为本地资源
	public function toLocal(){

	}

	public static function getResource($key) {
		$instance = null;
		switch ($key) {
			case 'keyword'  : $instance = new KeyWordResource(); break;
			case 'module'   : $instance = new ModuleResource(); break;
			case 'news'      : $instance = new NewsResource(); break;
			case 'video'    : $instance =new VideoResource(); break;
			case 'voice'    : $instance =new VoiceResource(); break;
			case 'image'    : $instance =new ImageResource(); break;
		}
		return $instance;
	}
}


class KeyWordResource extends Resource {

	public function getResources() {
		$keyword = addslashes($this->query('keyword',''));
		$pindex = $this->getCurrentPage();
		$psize = 24;

		$condition = array('uniacid' => $this->uniacid, 'status' => 1);
		if (!empty($keyword)) {
			$condition['content like'] = '%'.$keyword.'%';
		}

		$keyword_lists = pdo_getslice('rule_keyword', $condition, array($pindex, $psize), $total, array(), 'id');

		$result = array(
			'items' => $keyword_lists,
			'pager' => pagination($total, $pindex, $psize, '', array('before' => '2', 'after' => '3', 'ajaxcallback'=>'null','isajax'=>1)),
		);
		return $result;
	}
}

/**
 *  模块资源获取
 * Class ModuleResource
 */
class ModuleResource extends Resource {

	public function getResources() {

	}
}

/**
 *  视频资源获取
 * Class VideoResource
 */
class VideoResource extends Resource {

	protected $type = 'video';


	/***
	 *  获取视频数据
	 */
	public function getResources() {

		$server = $this->isLocal()? MATERIAL_LOCAL:MATERIAL_WEXIN;
		$page_index = $this->getCurrentPage();
		$page_size = 10;
		$material_news_list = material_list($this->type, $server, array('page_index' => $page_index, 'page_size' => $page_size),true);

		$material_list = $material_news_list['material_list'];
		$pager = $material_news_list['page'];
		return array('items'=>$material_list,'pager'=>$pager);

	}


}

/**
 *  图文资源获取
 * Class NewsResource
 */
class NewsResource extends Resource {

	public function getResources() {
		$server = $this->isLocal()? MATERIAL_LOCAL:MATERIAL_WEXIN;
		$page_index = $this->getCurrentPage();
		$page_size = 24;
		$search = addslashes($this->query('keyword'));
		$material_news_list = material_news_list($server, $search, array('page_index' => $page_index, 'page_size' => $page_size),true);
		$material_list = $material_news_list['material_list'];
		$pager = $material_news_list['page'];
		return array('items'=>$material_list,'pager'=>$pager);
	}
}

class VoiceResource extends VideoResource {
	 protected $type = 'voice';
}

class ImageResource extends Resource {
	protected $type = 'image';

	private $pagesize = 24;

	/**
	 *  加载本地图
	 */
	private function loadLocalImage()
	{
		$page = $this->getCurrentPage();
		$page = max(1, $page);

		$condition = ' WHERE uniacid = :uniacid AND type = :type';
		$params = array(':uniacid' => $this->uniacid, ':type' => 1);

		$year = intval($this->query('year'),0);
		$month = intval($this->query('month'),0);
		if ($year > 0 && $month > 0) {
			$starttime = strtotime("{$year}-{$month}-01");
			$endtime = strtotime("+1 month", $starttime);
			$condition .= ' AND createtime >= :starttime AND createtime <= :endtime';
			$params[':starttime'] = $starttime;
			$params[':endtime'] = $endtime;
		}

		$sql = 'SELECT * FROM ' . tablename('core_attachment') . " {$condition} ORDER BY id DESC LIMIT " . (($page - 1) * $this->pagesize) . ',' . $this->pagesize;
		$list = pdo_fetchall($sql, $params, 'id');
		foreach ($list as &$item) {
			$item['url'] = tomedia($item['attachment']);
			unset($item['uid']);
		}
		$total = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('core_attachment') . " {$condition}", $params);
		return array(
			'items'=>$list,
			'pager'=>pagination($total, $page, $this->pagesize, '', array('before' => '2', 'after' => '3', 'ajaxcallback'=>'null'))
		);
	}

	/**
	 *
	 * @return array
	 */
	private function loadWxImage()
	{
		$server = MATERIAL_WEXIN;
		$page_index = $this->getCurrentPage();
		$material_news_list = material_list($this->type, $server, array('page_index' => $page_index, 'page_size' => $this->pagesize));

		$material_list = $material_news_list['material_list'];
		$pager = $material_news_list['page'];
		// 因 meterial.js finish 输出的内容需要 url

		foreach ($material_list as &$meterial) {
			$meterial['attach'] = tomedia($meterial['attachment'], true);
			$meterial['url'] = $meterial['attach'];
		}
		return array('items'=>$material_list,'pager'=>$pager);
	}

	public function getResources() {

		if($this->isLocal())
		{
			return $this->loadLocalImage();
		}
		return $this->loadWxImage();
	}

	// 转为微信资源
	public function toWx() {

		$resource_id = $this->getResourceId();
		$attach = pdo_get('core_attachment',['id'=>$resource_id]);
		if($attach)
		{
			$path = $attach['attachment'];
			$real_path = $this->localPath($path);
			Storage::driver('imagewx')->put($path);
		}
	}

	public function toLocal() {

	}
}
