<?php
namespace OpenBible\Controllers;

use Silex\Controller;
use OpenBible\Utilities;
use OpenBible\Models\Version;
use OpenBible\Models\SearchParser;
use OpenBible\Models\Bible;
use OpenBible\Models\Response;

class BibleController extends Controller{

	public $app;
	public $search;
	public $versions;
	public $response;
	
	public function __construct($app){
		//Assign Application
		$this->app = $app;
		
		//Check what versions are used
		$versions = $app['session']->get('versions');
		if(!empty($_REQUEST['versions'])){
			$this->versions = is_array($_REQUEST['versions'])? $_REQUEST['versions'] : explode(",",$_REQUEST['versions']);			
		}elseif(!empty($versions)){
			$this->versions = $versions;
		}else{
			$this->versions = array(8);
		}
		$app['session']->set('versions', $this->versions);
				
		//Init Response Object
		$this->response = new Response($app);
	}
	
	public function index(){
		return $this->byBook(1);
	}
	
	public function bySearch($query){
		$this->search = $query;
		
		$parser = new SearchParser($query);
		$parsed = $parser->parse();
		
		$texts = array();
		if(!empty($parsed)){
			if(is_string($parsed)){
				foreach($this->versions as $version){
					$bible = new Bible($this->app, $version);
					$bible->load();
					$bible->Active = true;
					$bible->searchByWord($parsed);
					$texts[] = $bible;
				}
			}elseif(is_array($parsed)){
				foreach($this->versions as $version){
					$bible = new Bible($this->app, $version);
					$bible->load();
					$bible->Active = true;					
					$bible->get($parsed[0]['book'], $parsed[0]['chapter']);
					$texts[] = $bible;
				}
			}
		}
		//Add vars that can be json_encoded
		$this->response->addVars(array(				
				'title'=>$this->search,
				'current_url'=>'search/'.$this->search
		));
		
		$this->response->render('index.twig', array(
				'texts'=>$texts,
				'books'=>Bible::getAllBooks($this->app),
				'versions'=>Bible::getVersions($this->app, $this->versions),
				'search'=>$this->search
		));
	}
	
	public function byBook($book){
		return $this->byBookChapter($book, 1);
	}
	
	public function byBookChapter($rqbook, $rqchapter){
		$texts = array();
		
		$this->search = $rqbook ." ". $rqchapter;
		$book = Bible::getBook($this->app, $rqbook);
		
		foreach($this->versions as $version){
			$bible = new Bible($this->app, $version);
			$bible->load();
			$bible->Active = true;
			$bible->get($rqbook, $rqchapter ? $rqchapter : 1);
			$texts[] = $bible;
		}
		
		if(count($texts)){
			$this->search = $book->Names['dutch']." ".$rqchapter;
		}
		
		//Add vars that can be json_encoded
		$this->response->addVars(array(
			'title'=> $book->Names['dutch'] ." ".$rqchapter,
			'current_url'=> "/".$book->Names['dutch']."/".$rqchapter
		));
		
		//Render Html
		$this->response->render('index.twig', array(
				'rqbook'=>$book,
				'rqchapter'=>$rqchapter,
				'search'=>$this->search,
				'texts'=>$texts,
				'books'=>Bible::getAllBooks($this->app, $bible),
				'versions'=>Bible::getVersions($this->app, $this->versions)
		));
		
	}
	
	public function response(){
		return $this->response->get();
	}
}