<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/27
 * Time: 16:23
 */

namespace We7\DI;


use We7\DI\TestInterface;

class Test
{
	private $interface = null;
	public function __construct(TestInterface $interface)
	{
		$this->interface = $interface;
	}

	public function testDI() {
		echo '依赖注入start'.PHP_EOL;
		$this->interface->hello('hello method');
	}
}