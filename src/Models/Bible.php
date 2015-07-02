<?php
namespace OpenBible\Models;

use OpenBible\Utilities;
class Bible{
	
	public $Id;
	public $Table;
	public $Abbr;
	public $Lang;
	public $Name;
	public $Description;
	public $Url;
	public $Publisher;
	public $Copyright;
	public $CopyrightStatement;
	public $Books = array();
	public $Active;
	private $app;
	
	public function __construct($app, $id = 8){
		$this->app = $app;
		$this->Id = $id;
	}
	
	/**
	 * TODO: Function Description
	 * @param Var Description
	 */
	public function load(){
		$id = $this->Id ? $this->Id : 8;
		$query = "SELECT * FROM bible_version_key WHERE id=".$id;
		$rversion = $this->app['db']->fetchAssoc($query);
		if($rversion){
			$this->Id = $rversion['id'];
			$this->Table = $rversion['table'];
			$this->Abbr = $rversion['abbreviation'];
			$this->Lang = $rversion['language'];
			$this->Name = $rversion['version'];
			$this->Description = $rversion['info_text'];
			$this->Url = $rversion['info_url'];
			$this->Publisher = $rversion['publisher'];
			$this->Copyright = $rversion['copyright'];
			$this->CopyrightStatement = $rversion['copyright_info'];
		}else{
			show_error("Deze versie bestaat niet.");
		}
	}
	
	/**
	 * TODO: Function Description
	 * @param Var Description
	 */
	public static function getBook($app, $book, $bible = null){
		$query = "SELECT * FROM books WHERE 1=1";
		if(is_numeric($book)){
			$where = " AND b=".$book;
		}else{
			$where = " AND abbreviation='".$book."'";
			$where .= " OR dutch LIKE '".$book."%'";
			$where .= " OR english LIKE '".$book."%'";
		}
		$result = $app['db']->fetchAssoc($query.$where);
		
		if(empty($result)){
			\OpenBible\Utilities::output("Het boek '".ucfirst($book)."' bestaat niet.");
			return false;
		}else{
			$b = new Book($app, $bible);
			$b->Id = $result['b'];
			$b->Abbr = $result['abbreviation'];
			$b->Names = array(
				'english'=>$result['english'],
				'dutch'=>$result['dutch']	
			);
			$b->CountChapters();
			return $b;
		}
	}
	
	/**
	 * TODO: Function Description
	 * @param Var Description
	 */
	public static function getAllBooks($app, $bible = null){
		$books = array();
		$query = "SELECT * FROM books";
		$result = $app['db']->fetchAll($query);
		foreach($result as $rbook){
			$b = new Book($app);
			$b->Id = $rbook['b'];
			$b->Abbr = $rbook['abbreviation'];
			$b->Names = array(
					'english'=>$rbook['english'],
					'dutch'=>$rbook['dutch']
			);
			$b->ChapterCount = $app['db']->fetchColumn("SELECT MAX(c) FROM t_hsv WHERE b=".$rbook['b']);
			
			if(isset($bible)){
				if(array_key_exists($b->Id, $bible->Books)){
					$b->Active = true;
				}
			}
			$books[$b->Id] = $b;
		}
		return $books;
	}
	
	/**
	 * TODO: Function Description
	 * @param Var Description
	 */
	public function get($book, $chapter, $verses = null){
		$book = Bible::getBook($this->app, $book, $this);
		$book->Active = true;
		$book->LoadChapter($chapter);
		$this->Books[$book->Id] = $book;
	}
	
	/**
	 * TODO: Function Description
	 * @param Var Description
	 */
	public static function getVersions($app, $active){
		$query = "SELECT * FROM bible_version_key";
		$result = $app['db']->fetchAll($query);	
		$versions = array();	
		foreach($result as $rversion){
			$bible = new Bible($app);
			$bible->Id = $rversion['id'];
			$bible->Table = $rversion['table'];
			$bible->Abbr = $rversion['abbreviation'];
			$bible->Lang = $rversion['language'];
			$bible->Name = $rversion['version'];
			$bible->Description = $rversion['info_text'];
			$bible->Url = $rversion['info_url'];
			$bible->Publisher = $rversion['publisher'];
			$bible->Copyright = $rversion['copyright'];
			$bible->CopyrightStatement = $rversion['copyright_info'];
			if(in_array($bible->Id, $active)){
				$bible->Active = true;
			}
			$versions[$bible->Abbr] = $bible;
		}
		return $versions;
	}
	
	/**
	 * TODO: Function Description
	 * @param Var Description
	 */
	public function searchByWord($query){
		$from = " t_".$this->Table;
		$where = " WHERE ";

		$parts = explode(";", $query);
		for($i = 0; $i < count($parts); $i++){
			if($i > 0)
				$where .= " OR ";
			
			$part = trim($parts[$i]);
			$where .= " t LIKE '%".$part."%'  ";
		}
		
		$order = " ORDER BY b, c, v";
		$query = "SELECT * FROM ".$from.$where.$order;//." LIMIT 0,50 ";
		$verses = $this->app['db']->fetchAll($query);
		if(!empty($verses)){
			foreach($verses as $verse){
				if(array_key_exists($verse['b'], $this->Books)){
					$this->Books[$verse['b']]->AddVerse($verse);
				}else{
					$book = self::getBook($this->app, $verse['b'], $this);
					$book->AddVerse($verse);
					$this->Books[$verse['b']] = $book;						
				}					
			}
		}
		
	}
}