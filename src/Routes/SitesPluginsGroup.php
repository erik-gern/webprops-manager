<?php

namespace PropMgr\Routes;

use PropMgr\JsonResponse;
use PropMgr\DBPersistence;
use PropMgr\Middleware\CheckRecordExists;
use PropMgr\MiddleWare\CanonicalizeSort;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;
use PDO;

class SitesPluginsGroup
{
	public function __construct($db) {
		global $log;
		$this->db = $db;
		
		$this->vSitesPersistence = new DBPersistence(
			$this->db,
			'vSites',
			'id',
			['title']
		);
		$this->vSitesPersistence->addLogger($log);
		
		$this->vPluginsPersistence = new DBPersistence(
			$this->db,
			'vPlugins',
			'id',
			['title']
		);
		$this->vPluginsPersistence->addLogger($log);
		
		$this->vSitesPluginsPersistence = new DBPersistence(
			$this->db,
			'vSites_Plugins',
			['site_id', 'plugin_id'],
			[
				'site_id',
				'plugin_id',
				'is_active',
				'can_update',
				'site_title',
				'plugin_title',
			]
		);
		$this->vSitesPluginsPersistence->addLogger($log);		
		
		$this->sitesPluginsPersistence = new DBPersistence(
			$this->db,
			'Sites_Plugins',
			['site_id', 'plugin_id'],
			[
				'site_id',
				'plugin_id',
				'is_active',
				'can_update',
			]
		);		
		$this->sitesPluginsPersistence->addLogger($log);
		
	}
	
	public function __invoke($ctx) {
		// no plugin id
		$ctx->group('/{site_id}/plugins', function($group){
			$group->get('', [$this, 'list']);
		})
			->add(new CanonicalizeSort('sort'))
			->add(new CheckRecordExists($this->vSitesPersistence, 'site_id'));
		
		// with plugin id
		$ctx->group('/{site_id}/plugins/{plugin_id}', function($group){
			$group->get('', [$this, 'get']);
			$group->post('', [$this, 'add'])
				->add([$this, 'validate']);
			$group->put('', [$this, 'update'])
				->add([$this, 'validate']);
			$group->delete('', [$this, 'delete']);
		})
			->add(new CheckRecordExists($this->vSitesPersistence, 'site_id'))
			->add(new CheckRecordExists($this->vPluginsPersistence, 'plugin_id'));
	}
	
	public function list(Request $request, Response $response, $args): Response {	
		global $log;	
		$sort = (array) $request->getAttribute('sort');
		try {
			$records = $this->vSitesPluginsPersistence->list(
				['site_id' => $args['site_id']], 
				$sort
			);
		}
		catch (any $e) {
			$log->error($e);
			$response = $response->withStatus(500);
			$response->getBody()->write((string) new JsonResponse([
				'error' => 'Could not get records',
			]));
			return $response;
		}	
		
		$response->getBody()->write((string) new JsonResponse([
			'records' => $records,
		]));
		return $response;		
	}
	
	public function validate(Request $request, RequestHandler $handler) {
		// validation
		$errors = [];
		$params = (array) $request->getParsedBody();
		if (!isset($params['is_active'])) {
			$errors[] = 'is_active must be set';
		}
		elseif (intval($params['is_active']) != 0 && intval($params['is_active']) != 1) {
			$errors[] = 'is_active must be either 0 or 1';
		}
		if (!isset($params['can_update'])) {
			$errors[] = 'can_update must be set';
		}
		elseif (intval($params['can_update']) != 0 && intval($params['can_update']) != 1) {
			$errors[] = 'can_update must be either 0 or 1';
		}
		if (count($errors) > 0) {
			$response = new SlimResponse();
			$response = $response->withStatus(400);
			$response->getBody()->write((string) new JsonResponse([
				'status' => 'error',
				'error' => 'Invalid input',
				'errors' => $errors,
			]));
			return $response;
		}		
		
		$response = $handler->handle($request);
		return $response;
	}
	
	public function add(Request $request, Response $response, $args): Response {
		global $logs;
		$params = (array) $request->getParsedBody();
		try {
			$this->sitesPluginsPersistence->create($params, [
				'site_id' => $args['site_id'],
				'plugin_id' => $args['plugin_id']
			]);
		}
		catch (any $e) {
			$log->error($e);
			$response = $response->withStatus(500);
			$response->getBody()->write((string) new JsonResponse([
				'status' => 'error',
				'error' => 'Could not insert record',
			]));
			return $response;
		}
		
		$response = $response->withStatus(201);
		$response->getBody()->write((string) new JsonResponse([
			'status' => 'success',
		]));
		return $response;
		
	}
	
	public function get(Request $request, Response $response, $args): Response {
		global $log;
		try {
			$record = $this->vSitesPluginsPersistence->get($args);
		}
		catch (any $e) {
			$log->error($e);
			$response = $response->withStatus(500);
			$response->getBody()->write((string) new JsonResponse([
				'error' => 'Could not get record',
				'args' => $args,
			]));
			return $response;
		}	
		
		$response->getBody()->write((string) new JsonResponse([
			'record' => $record,
		]));
		return $response;	
	}
	
	public function update(Request $request, Response $response, $args): Response {	
		global $log;	
		$params = (array) $request->getParsedBody();
		try {
			$this->sitesPluginsPersistence->update($args, $params);
		}
		catch (any $e) {
			$log->error($e);
			$response = $response->withStatus(500);
			$response->getBody()->write((string) new JsonResponse([
				'status' => 'error',
				'error' => 'Could not update record',
			]));
			return $response;
		}
		
		$response->getBody()->write((string) new JsonResponse([
			'status' => 'success',
		]));
		return $response;

	}
	
	public function delete(Request $request, Response $response, $args): Response {
		global $log;
		try {
			$rowsAffected = $this->sitesPluginsPersistence->delete($args);
		}
		catch (any $e) {
			$log->error($e);
			$response = $response->withStatus(500);
			$response->getBody()->write((string) new JsonResponse([
				'status' => 'error',
				'error' => 'Could not delete record'
			]));
			return $response;
		}
		$response->getBody()->write((string) new JsonResponse([
			'status' => 'success',
			'rows_affected' => $rowsAffected
		]));
		return $response;
	}
	
}
