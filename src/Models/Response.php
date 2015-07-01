<?php
namespace OpenBible\Models;

class Response{
	
	public $app;
	public $async = false;
	public $html;
	public $tpls = array();
	public $vars = array();
	public $title;
	
	public function __construct($app){
		//Assign Application
		$this->app = $app;
				
		//Check if is ajax request
		if(!empty($_REQUEST['async']))
			$this->async = $_REQUEST['async'] == 1 ? true : false;		

		//Set Urls
		$this->vars['current_url'] = $_SERVER['REQUEST_URI'];
		$this->vars['base_url'] = str_replace("index.php", '', $_SERVER['SCRIPT_NAME']);
		$this->vars['site_url'] = "http://".$_SERVER['HTTP_HOST'].$this->vars['base_url'];

	}
	
	/**
	 * Render template with given vars
	 * @param strig $tpl: name of template file (Ex.: index.tpl)
	 * @param unknown $vars
	 */
	public function render($tpl, $vars){
		//Add vars
		$vars = array_merge($this->vars, $vars);	
		
		//Render Html from template
		$tpls[$tpl] = $this->app['twig']->render($tpl, $vars);
		
		//Set response Html
		$this->html = $tpls[$tpl];
			
	}
	
	/**
	 * Adds vars to list
	 * !Important: Only set values that can be json_encoded!
	 * @param array $vars: Json Encodable vars
	 */
	public function addVars($vars){
		if(is_array($this->vars))
			$this->vars = array_merge($this->vars, $vars);
		else
			$this->vars = $vars;
	}
	
	public function get(){
		if($this->async){
			$vars = $this->vars;
			if(!empty($this->html))
				$vars['html'] = $this->html;
							
			header('Content-Type: application/json');			
			return json_encode($vars);
		}
		
		return $this->html;
	}
}