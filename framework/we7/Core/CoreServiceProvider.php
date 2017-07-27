<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/27
 * Time: 14:17
 */

namespace We7\Core;


use We7\DI\TestImpl;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use Illuminate\Support\ServiceProvider;
use PDO;

class CoreServiceProvider extends ServiceProvider
{


	public function boot() {

		Model::setConnectionResolver($this->app['db']);

//		Model::setEventDispatcher($this->app['events']);
	}
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() {

		$this->registerDB();

		$this->app->bind('We7\DI\TestInterface',function(){
			return new TestImpl();
		});

		$this->app->bind('test',function($app){
			return $app->make('We7\DI\Test');
		});

	}

	/**
	 *  注册数据库 服务到容器中
	 *  使用方式 app('db')->table(tablename)->where()->get();
	 */
	private function registerDB() {
		if ( ! $this->app->bound('config'))
		{
			$this->app->instance('config', new Fluent);
		}

		$this->app->singleton('db.factory', function($app) {

			$this->app['config']['database.fetch'] = PDO::FETCH_ASSOC;
			$this->app['config']['database.default'] = 'default';
			$factory = new ConnectionFactory($app);
			$this->addConnection($this->dbconfig());//加入数据库配置
			return $factory;
		});


		$this->app->singleton('db', function($app) {

			return new DatabaseManager($app, $app['db.factory']);
		});
	}


	public function addConnection(array $config, $name = 'default')
	{
		$connections = $this->app['config']['database.connections'];

		$connections[$name] = $config;

		$this->app['config']['database.connections'] = $connections;
	}
	/***
	 *  初始化laravel 数据
	 */
	private function dbconfig()
	{
		global $_W;
		$config = $_W['config']['db']['master'];
		if(empty($config)) {
			$config = $GLOBALS['_W']['config']['db'];
		}
		$database = [
			'driver'    => 'mysql',
			'host'      =>   $config['host'],
			'database'  =>  $config['database'],
			'username'  =>  $config['username'],
			'password'  =>  $config['password'],
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    =>  $config['tablepre'],
		];

		return $database;

	}
}