<?php
namespace OpenBible\Models;

class SearchParser{

	public $query;
	public $regex = "/(?:\d\s*)?[A-Z]?[a-z]+\s*\d+(?:[:-]\d+)?(?:\s*-\s*\d+)?(?::\d+|(?:\s*[A-Z]?[a-z]+\s*\d+:\d+))?/";
	
	public function __construct($query = null){
		$this->query = $query;
	}
	
	/**
	 * TODO: Function Description
	 * @param Var Description
	 */
	public function parse($query = null){
		if($query == null){
			$query = $this->query;
		}
		
		if(preg_match($this->regex, $query)){
			$texts = array();
			$verses = array();
			foreach(explode(";", $query) as $part){
				$verses = array();
				$book_verse = explode(":", $part);				
				if(count($book_verse) == 2){
					foreach(explode(",", $book_verse[1]) as $vers){
						$verseparts = explode("-", $vers);
						$start = $verseparts[0];						
						$end = count($verseparts) == 2 ? $verseparts[1] : null;
						$verses[] = array(
							'start'=> $start,
							'end'=> $end	
						);
					}
				}
				$book_chapter = explode(" ", $book_verse[0]);			
				if(count($book_chapter) == 3)
					$book = $book_chapter[0]." ".$book_chapter[1];
				else 
					$book = $book_chapter[0];
				
				$chapter = substr(rtrim(end($book_chapter)), -1);
				$chapter = is_numeric($chapter) ? $chapter : 1;
				
				$texts[] = array(
					'book' => $book,
					'chapter'=>$chapter,
					'verses'=>$verses
				);
			}
			return $texts;
		}else{
			return $query;
		}
	}
}