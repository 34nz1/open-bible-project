<?php
namespace OpenBible\Models;

class Verse{
	public $Id;
	public $Book;
	public $Chapter;
	public $Number;
	public $Text;
	public $References;
	private $app;
	
	public function __construct($app){
		$this->app = $app;
	}
}