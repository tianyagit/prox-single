<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');


class JobTable extends We7Table {

	protected $tableName = 'job';
	protected $field = array('type', 'payload', 'status', 'handled', 'uniacid', 'title', 'total', 'create_time', 'end_time', 'update_time');

	protected $default = array('status'=>0, 'handed'=>0,'total'=>0, 'create_time'=>'custom', 'update_time'=>'custom');
	const DELETE_ACCOUNT = 10;
	const SYNC_FANS = 20;

	/**
	 *  使用默认创建时机
	 * @return int
	 */
	protected function defaultCreateTime() {
		return TIMESTAMP;
	}

	/** 默认更新时间
	 * @return int
	 */
	protected function defaultUpdateTime() {
		return TIMESTAMP;
	}
	/**
	 * 获取所有任务
	 * @return mixed
	 */
	public 	function jobs() {
		return $this->where('status', 0)->getall();
	}

	/**
	 *  所有任务
	 */
	public function alljobs() {
		return $this->getall();
	}

	/*
	 *  设置正在处理中
	 */
	public function setDoing($id, $doing)
	{
		return table('job')->fill('doing', intval($doing) == 1 ? 1 : 0)->where('id', $id)->save();
	}

	/**
	 *  是否有已存在的任务
	 * @param $uniacid
	 * @param $type
	 */
	public function exitsJob($uniacid, $type)
	{
		$result = table('job')->where('uniacid', $uniacid)->where('type', $type)->get();
		return !empty($result);
	}
	/**
	 *  创建一个删除公众号素材的任务
	 * @param $uniacid
	 */
	public function createDeleteAccountJob($uniacid, $accountName, $total = 0)
	{
		// 任务已存在
		if ($this->exitsJob($uniacid, self::DELETE_ACCOUNT)) {
			return error(1, '任务已存在');
		}

		$data = array(
			'type' => self::DELETE_ACCOUNT,
			'title'=> "删除{$accountName}的公众号数据",
			'uniacid'=>$uniacid,
			'total'=> $total
		);
		return $this->createJob($data);
	}

	/**
	 *  创建同步粉丝任务
	 * @param $uniacid
	 */
	public function createSyncFans($uniacid, $accountName, $total ) {
		// 任务已存在
		if ($this->exitsJob($uniacid, self::SYNC_FANS)) {
			return error(1, '同步任务已存在');
		}
		$data = array(
			'type' => self::SYNC_FANS,
			'title'=> "同步 $accountName ($uniacid) 的公众号粉丝数据",
			'uniacid'=>$uniacid,
		);
		return $this->createJob($data);
	}

	private function createJob($data)
	{
		$this->fill($data);
		$result = $this->save();
		return $result;
	}
}