<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/27
 * Time: 11:00
 */

namespace We7;


use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use We7\Core\CoreServiceProvider;

class App extends Container
{
	protected $loadedProviders = [];
	public function __construct() {

		$this->bootstrap();
	}
	/**
	 *  bootstrap
	 */
	public function bootstrap() {

		$this->registerBaseBindings();
		$this->register(new CoreServiceProvider($this));
	}

	/**
	 * 注册 ServceProvider
	 *
	 * @param  \Illuminate\Support\ServiceProvider|string  $provider
	 * @param  array  $options
	 * @param  bool   $force
	 * @return \Illuminate\Support\ServiceProvider
	 */
	public function register($provider, $options = [], $force = false)
	{
		if (! $provider instanceof ServiceProvider) {
			$provider = new $provider($this);
		}

		if (array_key_exists($providerName = get_class($provider), $this->loadedProviders)) {
			return;
		}

		$this->loadedProviders[$providerName] = true;

		if (method_exists($provider, 'register')) {
			$provider->register();
		}

		if (method_exists($provider, 'boot')) {
			return $this->call([$provider, 'boot']);
		}
	}

	/**
	 * Register the basic bindings into the container.
	 *
	 * @return void
	 */
	protected function registerBaseBindings() {

		static::setInstance($this);
		$this->instance('app', $this);
		$this->instance('Illuminate\Container\Container', $this);
	}


	public function run()
	{
		
	}
}