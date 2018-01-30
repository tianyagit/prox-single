<?php
namespace MicroEngine;


class GroupTest extends MyUnitTester{


	public function testGroup() {

		$params = http_build_query(array('eid'=>''));
		var_dump($params);
		exit;
		if (preg_match('/^[0-9]{1,2}\.[0-9]{1,2}(\.[0-9]{1,2})?$/', '1.20')) {
			echo '123456';
		}
		exit;
		var_dump(intval('-1'));
		$table = table('attachmentgroup');
		$table->fill(array(
			'uid' => 1,
			'uniacid'=>1,
			'name'=>1,
			'type'=> 1
		));
		$result = $table->save();
		if (is_error($result)) {
//			iajax($result['errno'], $result['message']);
		}

	}

}