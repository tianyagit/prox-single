<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/27
 * Time: 16:21
 */

namespace We7\DI;


class TestImpl implements TestInterface
{

	public function hello($data)
	{
		echo $data;
		echo '实现类';
	}
}