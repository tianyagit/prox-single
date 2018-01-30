<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/12/14
 * Time: 14:52
 */

namespace MicroEngine;

if(!function_exists('dd')) {
	function dd($val) {
		var_dump($val);
		exit;
	}
}
class ValidatorTest extends MyUnitTester {

	public function setUp() {
		parent::setUp();
		load()->classs('validator');
		load()->classs('uploadedfile');
	}

	private function valid($data, $rule, $message = array()) {
		return \Validator::create($data, $rule, $message)->valid();
	}
	public function testRequired() {
		$data = array('a'=>'12', 'b'=>3, 'c'=>'', 'd'=>array(), 'e'=>null);
		$rule = array('a'=>'required', 'c'=>'required', 'd'=>'required', 'e'=>'required');
		$valid = $this->valid($data, $rule);
		dd($valid);
		$this->assertTrue(is_error($this->valid($data, $rule)));
	}

	public function testInt() {
		$data = array('a'=>'12a', 'b'=>3);
		$rule = array('a'=>'required|integer', 'b'=>'int', 'c'=>'int');
		$valid = $this->valid($data, $rule);
		$this->assertFalse(is_error($valid));
	}

	public function testBool() {
		$data = array('a'=>'1', 'b'=>2, 'c'=>'true', 'd'=>false, 'e'=>0);
		$rule = array('a'=>'bool', 'b'=>'bool', 'c'=>'bool', 'd'=>'bool', 'e'=>'bool');
		$valid = $this->valid($data, $rule);
		dd($valid);
	}

	public function testNumbric() {
		$data = array('a'=>'12.55', 'b'=>33, 'c'=>123.55, 'd'=>'sad.3');
		$rule = array('a'=>'numeric', 'b'=>'numeric', 'c'=>'numeric', 'd'=>'numeric');
		$valid = $this->valid($data, $rule);
		dd($valid);
	}

	public function testString() {
		$data = array('a'=>'', 'b'=>3, 'c'=>'');
		$rule = array('a'=>'required|string', 'b'=>'string', 'c'=>'string');
		$valid = $this->valid($data, $rule);
		dd($valid);
	}

	public function testJson() {
		$data = array('a'=>'{"a":"1"}', 'b'=>"{'a':'1'}");
		$rule = array('a'=>'json', 'b'=>'json');
		$valid = $this->valid($data, $rule);
		dd($valid);
	}

	public function testArray() {
		$data = array('a'=>array(), 'b'=>array('1','b'), 'c'=>array(), 'd'=>1);
		$rule = array('a'=>'required|array', 'b'=>'array', 'c'=>'array', 'd'=>'array');
		$result = $this->valid($data, $rule);
		dd($result);
	}

	public function testSizeBetween() {
		$data = array('a'=>10, 'b'=>'123456', 'c'=>array(1,2,3), 'd'=> new \SplFileInfo(__DIR__.'/hello.txt'));
		$rule = array('a'=>'size:10|between:1,9', 'b'=>'size:7|between:1,6', 'c'=>'size:4', 'd'=>'size:10');
		$validator = \Validator::create($data, $rule,  array('d.size'=> 'd 大小必须是10KB'));
		$result = $validator->valid();
		if (is_error($result)) {
			var_dump($validator->error());
		}
	}

	public function testMinMax() {
		$data = array('a'=>11,
			'b'=>'123456',
			'c'=>array(1,2,3),
			'd'=> new \SplFileInfo(__DIR__.'/hello.txt'));

		$rule = array('a'=>'min:1|max:10',
			'b'=>'min:1|max:7',
			'c'=>'min:1|max:3|size:4',
			'd'=>'min:1|max:5|size:10');

		$validator = \Validator::create($data, $rule, array('b.max'=>'b的长度不能大于7',
			'c.size'=>'c 数组长度不能大于4',
			'd.size'=> 'd 大小必须是10KB'));

		$validator->valid();
		var_dump($validator->error());
	}

	public function testInNotIn() {
		$data = array('a'=> '7', 'b'=>8, 'c'=>9);
		$rule = array('a'=>'notin:1,2,3', 'b'=>'notin:4,5,7',
			'c'=>array(array('name'=>'notin', 'params'=>array(7,8,9))));
		dd($this->valid($data, $rule, array('c'=>'c 必须不在7,8,9 范围内')));
	}

	public function testDate() {
		$data = array('a'=>'2018-13-03');
		$rule = array('a'=>'date');
		dd($this->valid($data, $rule));
	}

	public function testAfterBefore() {
		$data = array('a'=>'2018-12-03');
		$rule = array('a'=>'date|after:2018-12-04|before:2018-12-01');
		dd($this->valid($data, $rule));
	}

	public function testSome() {
		$data = array('a'=>'1', 'b'=>1);
		$rule = array('a'=>'same:b');
		dd($this->valid($data, $rule, array('a'=>'a 和 b的值必须相等')));
	}

	public function testMobileIpEmail() {
		$data = array('a'=>'a@qq.com', 'b'=>'15522552c22', 'c'=>'125.0.1.1333');
		$rule = array('a'=>'email', 'b'=>'mobile', 'c'=>'ip');
		dd($this->valid($data, $rule));
	}

	public function testRegex() {
		$data = array('a'=>'1551351122');
		$rule = array('a'=> array(
			array('name'=>'regex', 'params'=> array('/^1[34578]\d{9}$/'))
		));
		dd($this->valid($data, $rule, array('a'=>'a不是有效的手机号')));
	}

	public function testCustom() {
		$data = array('a'=>'1551351122');
		$rule = array('a'=> 'exits');
		$validator = new \Validator($data, array('a'=>'exits'), array('a'=>'电话号码已存在'));
		$validator->addRule('exits', function($key, $value, $params, $validor){
			//验证不通过返回false 验证通过返回true
			//pdo_get().....
			return false;
		});
		dd($validator->valid());
	}

	public function testParams() {
		$data = array('a'=>'123');
		$rule = array('mobile');
		$validator = new \Validator($data, array('a'=> array('in:1243')));
		dd($validator->valid());
	}


}