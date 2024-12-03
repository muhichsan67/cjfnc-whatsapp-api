<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$cookies = !empty($_COOKIE['connection']) ? $_COOKIE['connection'] : 'default';

$active_group = $cookies;
$query_builder = TRUE;

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => '103.209.6.32:1521/brs',
	'username' => 'CJWA',
	'password' => 'CJWA',
	'database' => 'brs',
	'dbdriver' => 'oci8',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => '',
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
