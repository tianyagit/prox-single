<?php
/**
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
if ($action != 'material_post') {
	define('FRAME', 'account');
}
if ($action == 'qr') {
	define('FRAME', 'qr');
}