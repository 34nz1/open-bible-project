<?php 
require_once __DIR__.'/vendor/autoload.php';

use OpenBible\Utilities;
use OpenBible\OpenBible;

/*if($_SERVER['SERVER_SOFTWARE'] == "PHP 5.5.9 Development Server"){
	if (preg_match('/\.(?:png|jpg|jpeg|gif|js|css|otf)$/', $_SERVER["REQUEST_URI"])) {
		return false;
	}
}*/

/**
 * Return false if the requested file is available on the filesystem.
 * @see: http://silex.sensiolabs.org/doc/web_servers.html#php-5-4
 */
if (php_sapi_name() == 'cli-server') {
	$filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
	if (is_file($filename)) {
		return false;
	}
}

/**
 * Create and initialize OpenBible
 */
$app = new OpenBible();

if($app){
	$app['debug']=true;
	$app->initialize();
}else {
	return false;
}

?>