<?php
namespace OpenBible;

use Silex\Application;
use OpenBible\Controllers\BibleController;
use Silex\Provider;

class OpenBible extends Application{
	
	private $controller;
	
	public function initialize(){
		$this->initSessions();
		$this->initRoutes();
		$this->initDb();
		$this->initTemplating();
		$this->boot();
		$this->run();
	}
	
	private function initSessions(){
		//Register Security Provider
		$this->register(new Provider\SecurityServiceProvider());
		$this['security.firewalls'] = array(
			'secured_area' => array(
		        'pattern' => '^.*$',
		        'anonymous' => true,
		        'form' => array(
		            'login_path' => '/',
		            'check_path' => '/user/login_check',
		        ),
		        'logout' => array(
		            'logout_path' => '/user/logout',
		        ),
		        'users' => $this->share(function($this) { return $this['user.manager']; }),
		    ),
		);
		
		//Register Session Provider
		$this->register(new Provider\SessionServiceProvider());
		
		//Register User Provider
		$userProvider = new \SimpleUser\UserServiceProvider();
		$this->register($userProvider);
		$this['user.options'] = array(
				'templates' => array(
						'layout' => 'users/layout.twig',
						'register' => 'users/register.twig',
						'register-confirmation-sent' => 'users/register-confirmation-sent.twig',
						'login' => 'users/login.twig',
						'login-confirmation-needed' => 'users/login-confirmation-needed.twig',
						'forgot-password' => 'users/forgot-password.twig',
						'reset-password' => 'users/reset-password.twig',
						'view' => 'users/view.twig',
						'edit' => 'users/edit.twig',
						'list' => 'users/list.twig',
				),
		);
		
		$this->register(new Provider\ServiceControllerServiceProvider());
		$this->mount('/user', $userProvider);
	}
	
	private function initRoutes(){
		$app = $this;
		$this->get('/', function() use ($app){
			$controller = new BibleController($app);
			$controller->index();
			return $controller->response();
		});

		$this->get('/search/{query}', function($query) use ($app){
			$controller = new BibleController($app);
			$controller->bySearch($query);
			return $controller->response();
		});
			
		$this->get('/{book}', function($book) use ($app){
			$controller = new BibleController($app);
			$controller->byBook($book);
			return $controller->response();
		});
			
		$this->get('/{book}/{chapter}', function($book,$chapter) use ($app){
			$controller = new BibleController($app);
			$controller->byBookChapter($book,$chapter);
			return $controller->response();
		});
		
		$this->post('/update', function() use ($app){
			$controller = new BibleController($app);
			return "OK";
		});
	}
	
	private function initDb(){
		$this->register(new Provider\DoctrineServiceProvider(), array(
				'dbs.options' => array (
						'mysql_read' => array(
							'driver'    => 'pdo_mysql',
							'host'      => 'localhost',
							'dbname'    => '',
							'user'      => '',
							'password'  => '',
							'charset' 	=> 'UTF8'
						),
						'mysql_write' => array(
							'driver'    => 'pdo_mysql',
							'host'      => 'localhost',
							'dbname'    => 'root_obp',
							'user'      => '',
							'password'  => '',
							'charset' 	=> 'UTF8'
						),
				),
		));
	}
	
	private function initTemplating(){
		$this->register(new Provider\TwigServiceProvider(), array(
			'twig.path' => __DIR__.'/../views',
		));
		$this->register(new Provider\UrlGeneratorServiceProvider());
	}
}