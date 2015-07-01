<?php
namespace OpenBible;

class DBConfig{
	public static function getArray(){
		return array(
			'driver'    => 'pdo_mysql',
			'host'      => 'localhost',
			'dbname'    => '',
			'user'      => '',
			'password'  => '',
			'charset' 	=> 'UTF8'
		);
	}
}
