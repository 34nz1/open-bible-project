<?php
namespace OpenBible;

class Utilities{
	
	public static function output(){
		foreach(func_get_args() as $arg){
			print("<pre>");
			print_r($arg);
			print("</pre>");
		}
	}
	
	public static function zerofill($num, $zerofill = 3){
		return str_pad($num, $zerofill, '0', STR_PAD_LEFT);
	}
}