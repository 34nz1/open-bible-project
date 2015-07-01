<?php
namespace OpenBible\Models;

use OpenBible\Utilities;
class Book{
	
	public $Id;
	public $Names;
	public $Abbr;
	public $Bible;
	public $ChapterCount;
	public $Chapters = array();
	public $Sections;
	public $Active;
	private $app;
	
	public function __construct($app, $bible = null){
		$this->app = $app;
		$this->Bible = $bible ? $bible : new Bible($app);
	}
	
	public function LoadSections($book,$chapter){
		$query = "SELECT COUNT(*)
			FROM information_schema.tables 
			WHERE table_schema = DATABASE()
			AND table_name = 's_".$this->Bible->Table."';";
		$has_sections = $this->app['db']->fetchColumn($query);
		
		if($has_sections){
			$sections = array();
			$book = Utilities::zerofill($book, 2);
			$chapter = Utilities::zerofill($chapter);
		
			$query = "SELECT * FROM s_".$this->Bible->Table." WHERE 1=1";
			$query .= " AND sv LIKE '".$book.$chapter."%'";
			$result = $this->app['db']->fetchAll($query);
			
			foreach($result as $res){
				$section = new Section();
				$section->Id = $res['sid'];
				$section->Title = $res['title'];
				$section->StartVerse = substr($res['sv'], -2);
				$section->EndVerse = substr($res['ev'], -2);
				$sections[$res['sv']] = $section;
			}
			return $sections;
		}
		return null;
	}
	
	public function LoadChapter($number, $verses = true){
		if($number > $this->ChapterCount){
			return false;
		}else{
			$chapter = new Chapter();
			$chapter->Id = $number;
			$chapter->Verses = array();
			if($verses){
				$query ="SELECT * FROM  t_".$this->Bible->Table." WHERE b=".$this->Id." AND c=".$number;
				$result = $this->app['db']->fetchAll($query);
				foreach($result as $rawv){
					$verse = new Verse($this->app);
					$verse->Id = $rawv['id'];
					$verse->Book = $rawv['b'];
					$verse->Chapter = $rawv['c'];
					$verse->Number = $rawv['v'];
					$verse->Text = $rawv['t'];
					$chapter->Verses[$verse->Number] = $verse;
				}
			}
			$chapter->Sections = $this->LoadSections($this->Id, $number);
			$chapter->Active = true;
			$this->Chapters[$number] = $chapter;
			return $chapter;
		}
	}
	
	public function AddVerse($rawv){
		$verse = new Verse($this->app);
		$verse->Id = $rawv['id'];
		$verse->Book = $rawv['b'];
		$verse->Chapter = $rawv['c'];
		$verse->Number = $rawv['v'];
		$verse->Text = $rawv['t'];
					
		if(array_key_exists($verse->Chapter, $this->Chapters)){
			$this->Chapters[$verse->Chapter]->Verses[$verse->Number] = $verse;
		}else{
			$chapter = $this->LoadChapter($verse->Chapter, false);
			$chapter->Verses[$verse->Number] = $verse;
		}
	}
	
	public function CountChapters(){
		$this->ChapterCount = $this->app['db']->fetchColumn("SELECT MAX(c) FROM t_hsv WHERE b=".$this->Id);
	}
}