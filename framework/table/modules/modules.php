<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Modules;

class Modules extends \We7Table {
	protected $tableName = 'modules';
	protected $primaryKey = 'mid';
	protected $field = array(
		'name',
		'type',
		'title',
		'title_initial',
		'version',
		'ability',
		'description',
		'author',
		'url',
		'settings',
		'subscribes',
		'handles',
		'isrulefields',
		'issystem',
		'target',
		'iscard',
		'permissions',
		'wxapp_support',
		'account_support',
		'welcome_support',
		'webapp_support',
		'oauth_type',
		'phoneapp_support',
		'app_support',
	);

}