<?php

namespace PropMgr\Routes;

use PropMgr\JsonResponse;
use PropMgr\DBPersistence;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;

class StatsGroup
{
	public function __construct($db) {
		global $log;
		$this->db = $db;

		$this->vNodeModulesPersistence = new DBPersistence($this->db, 'vNodeModules', 'id');
		$this->vNodeModulesPersistence->addLogger($log);

		$this->vSitesPersistence = new DBPersistence($this->db, 'vSites', 'id');
		$this->vSitesPersistence->addLogger($log);
		
		$this->vPluginsPersistence = new DBPersistence($this->db, 'vPlugins', 'id');
		$this->vPluginsPersistence->addLogger($log);
		
		$this->vThemesPersistence = new DBPersistence($this->db, 'vThemes', 'id');
		$this->vThemesPersistence->addLogger($log);
		
		$this->vUsersPersistence = new DBPersistence($this->db, 'vUsers', 'id');
		$this->vUsersPersistence->addLogger($log);
	}
	
	public function __invoke($ctx) {
		$ctx->get('', [$this, 'getStats']);	
	}
	
	public function getStats (Request $request, Response $response): Response {
		
		$nodeModulesCount = $this->vNodeModulesPersistence->count();
		$sitesCount = $this->vSitesPersistence->count();
		$pluginsCount = $this->vPluginsPersistence->count();
		$themesCount = $this->vThemesPersistence->count();
		$usersCount = $this->vUsersPersistence->count();
		
		$response->getBody()->write((string) new JsonResponse([
			'stats' => [
				'nodemodules' => $nodeModulesCount,
				'sites' => $sitesCount,
				'plugins' => $pluginsCount,
				'themes' => $themesCount,
				'users' => $usersCount,
			],
		]));
		return $response;
	}
}