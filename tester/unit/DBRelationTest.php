<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/12/26
 * Time: 17:04
 */

namespace MicroEngine;


class DBRelationTest extends MyUnitTester{


	public function testOne2One() {

		/**
		 *  一对一关联  uni_account account
		 */
		$data = table('account')->with('baseaccount')->where('uniacid', 887)->get();
		var_dump($data);

		$data = table('account')->with('baseaccount')->where('uniacid', array(854, 855))->getall();
		var_dump($data);
	}

	public function testOneToMany() {
		/**
		 *  公众号下的所有菜单
		 */
		$data = table('account')->with('menus')->where('uniacid', 851)->get();
		var_dump($data);

		$data = table('account')->with('menus')->where('uniacid', array(851, 281))->getall();
		var_dump($data);


	}

	public function testManyToOne() {
		/**
		 *  某个菜单归属哪个公众号
		 */
		$data = table('menu')->with('uniaccount')->where('uniacid', 851)->getall();
		var_dump($data);
	}

	public function testMany2Many() {
//		load()->classs('query');
//		$query = new \Query();
//		$query->from('uni_group', 'three')
//			->innerjoin('uni_account_group', 'center')
//			->on(array('center.groupid' => 'three.id'))
//			->select('*')
//			->where('center.uniacid', array(887, 281));
//		$data = $query->getall();
//
//		var_dump($data);
//	exit;

		/**
		 *  公众号包含哪些应用权限组
		 */
		$data = table('account')->with('unigroup')->where('uniacid', array(887, 281))->getall();
		var_dump($data);

		$data = table('account')->with('unigroup')->where('uniacid', 281)->get();
		var_dump($data);
//		/**
//		 *  应用权限组包含哪些公众号
//		 */
		$data = table('unigroup')->with('uniaccounts')->where('id', 66)->get();
		var_dump($data);

		$data = table('unigroup')->with('uniaccounts')->where('id', array(66,64,62))->getall();
		var_dump($data);

	}

	public function test1() {
		$data = table('baseaccount')->where('acid', '100131')->get();
		var_dump($data);
	}

	public function testWithQuery() {
		$modules = table('module')->with(array('bindings' => function($query){
			return $query->where('entry', 'cover');
		}))->where('name', 'we7_community')->get();

		var_dump($modules);
	}

	public function testHas() {

	}
}