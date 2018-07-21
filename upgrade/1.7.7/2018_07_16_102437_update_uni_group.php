<?php

namespace We7\V177;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1531707877
 * @version 1.7.7
 */

class UpdateUniGroup {

	/**
	 *  执行更新
	 */
	public function up() {
        $data = pdo_getall('uni_group', array(), array('id', 'modules'));
        foreach ($data as $row) {
            if (empty($row['modules'])) {
                continue;
            }
            $row['modules'] = iunserializer($row['modules']);
            if (!empty($row['modules']) && !isset($row['modules']['modules'])) {
                $new_row = array('modules' => $row['modules'], 'wxapp' => $row['modules'], 'webapp' => $row['modules'], 'xzapp' => $row['modules'], 'phoneapp' => $row['modules']);
            } else {
                $new_row = array('modules' => array(), 'wxapp' => array(), 'webapp' => array(), 'xzapp' => array(), 'phoneapp' => array());
            }
            pdo_update('uni_group', array('modules' => iserializer($new_row)), array('id' => $row['id']));
        }
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		