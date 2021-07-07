<?php

require __DIR__.'/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use PropMgr\JsonResponse;
use PropMgr\Routes\NodeModulesGroup;
use PropMgr\Routes\SitesGroup;
use PropMgr\Routes\SitesPluginsGroup;
use PropMgr\Routes\SitesThemesGroup;
use PropMgr\Routes\SitesUsersGroup;
use PropMgr\Routes\StatsGroup;
use PropMgr\MiddleWare\ParseRequestBodyAsPostParams;

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);
$app->add(new ParseRequestBodyAsPostParams());

global $log;
$log = new Logger('default');
$logStr = __DIR__.'/../logs/'.strftime('%Y-%m-%d').'-%s.log';
$log->pushHandler(new StreamHandler(sprintf($logStr, 'default'), Logger::DEBUG));
$log->pushHandler(new StreamHandler(sprintf($logStr, 'warning'), Logger::WARNING));

$db = new PDO('sqlite:'.__DIR__.'/../data.db');

$app->group('/api', function($group) use ($db) {
	
	// TODO: use ping to trigger scheduled tasks
	$group->post('/ping', function(Request $request, Response $response){
		$response->getBody()->write((string) new JsonResponse([
			'time' => time(),
		]));
		return $response;
	});
	
	$group->group('/stats', new StatsGroup($db));

	$group->group('/sites', new SitesGroup($db));
	$group->group('/sites', new SitesPluginsGroup($db));
	$group->group('/sites', new SitesThemesGroup($db));
	$group->group('/sites', new SitesUsersGroup($db));
	
	$group->group('/nodemodules', new NodeModulesGroup($db));

	$group->get('/plugins', function(Request $request, Response $response) use ($db) {
		$stmt = $db->query("SELECT * FROM Plugins");
		$records = $stmt->fetchAll();
		$response->getBody()->write((string) new JsonResponse([
			'records' => $records,
		]));
		return $response;
	});

	$group->get('/themes', function(Request $request, Response $response) use ($db) {
		$stmt = $db->query("SELECT * FROM Themes");
		$records = $stmt->fetchAll();
		$response->getBody()->write((string) new JsonResponse([
			'records' => $records,
		]));
		return $response;
	});

	$group->get('/users', function(Request $request, Response $response) use ($db) {
		$stmt = $db->query("SELECT * FROM Users");
		$records = $stmt->fetchAll();
		$response->getBody()->write((string) new JsonResponse([
			'records' => $records,
		]));
		return $response;
	});

});

$log->notice('App initialized');

$app->run();
