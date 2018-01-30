<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/12/15
 * Time: 14:56
 */

namespace MicroEngine;


class DBOperaterTest extends MyUnitTester {

	public function testSave1() {
		$result = table('dboperator')->fillAge(55)->save();
		var_dump($result);
	}

	public function testSave() {
		$result = table('dboperator')->fill(array(
			'name'=>'3',
			'age'=>15
		))->save();

		$this->assertTrue(!is_error($result));
		$result = table('dboperator')->fill(array(
			'age'=>5
		))->save();
		$this->assertTrue(is_error($result));

		$result = table('dboperator')->fill(array(
			'age'=>5,
			'is_delete'=>5
		))->save();
		$this->assertTrue(is_error($result));

		$result = table('dboperator')->fill(array(
			'age'=>15,
			'name'=>'hello',
			'is_delete'=>1
		))->save();
		$this->assertTrue(!is_error($result));


		$result = table('dboperator')->fill(array(
			'age'=>15,
			'name'=>'hello2',
			'is_delete'=>0
		))->save();
		$this->assertTrue(!is_error($result));

		$result = table('dboperator')
			->whereType('tabletest')->getall();

		$this->assertTrue(count($result) == 3);

	}

	public function testSearch() {
		$result = table('dboperator')->whereName('hello')
			->whereType('tabletest')
			->whereAge(15)->getall();
		var_dump($result);
//		$this->assertTrue(count($result) == 1);
	}

	public function testUpdate() {
		$result = table('dboperator')
			->fillAge(30)
			->fillName('ahe')
//			->fill([])
			->whereType('tabletest')
			->whereName('hello')->save();
		$this->assertTrue($result == 1);

//		$update2 = table('dboperator')
//			->fillIsDelete(5)
//			->whereType('tabeltest')->save();
//
//		$this->assertTrue(is_error($result));

	}

	public function testDelete() {
		$result = table('dboperator')
			->whereName('hello2')
			->whereIsDelete(0)
			->whereAge(15)->delete();
		$this->assertTrue($result == 1);
	}



	public function testFind() {
		$data = table('dboperator')->getById(44);
		var_dump($data);
	}

	public function testGroup() {

		$table = table('attachment');
		$updated = $table->where('id', 10)->fill('group_id', 3)->save();
	}


}