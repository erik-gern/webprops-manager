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

class SitesUsersGroup
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
		
		$this->vUsersPersistence = new DBPersistence(
			$this->db,
			'vUsers',
			'id',
			['emailaddress']
		);
		$this->vUsersPersistence->addLogger($log);
		
		$this->vSitesUsersPersistence = new DBPersistence(
			$this->db,
			'vSites_Users',
			['site_id', 'user_id'],
			[
				'site_id',
				'user_id',
				'site_title',
				'emailaddress',
				'name_first',
				'name_last',
				'username',
				'role',
				'is_active',
				'is_employee'
			]
		);
		$this->vSitesUsersPersistence->addLogger($log);		
		
		$this->sitesUsersPersistence = new DBPersistence(
			$this->db,
			'Sites_Users',
			['site_id', 'user_id'],
			[
				'site_id',
				'user_id',
				'username',
				'role',
			]
		);		
		$this->sitesUsersPersistence->addLogger($log);
		
	}
	
	public function __invoke($ctx) {
		// no user id
		$ctx->group('/{site_id}/users', function($group){
			$group->get('', [$this, 'list']);
		})
			->add(new CanonicalizeSort('sort'))
			->add(new CheckRecordExists($this->vSitesPersistence, 'site_id'));
		
		// with user id
		$ctx->group('/{site_id}/users/{user_id}', function($group){
			$group->get('', [$this, 'get']);
			$group->post('', [$this, 'add'])
				->add([$this, 'validate']);
			$group->put('', [$this, 'update'])
				->add([$this, 'validate']);
			$group->delete('', [$this, 'delete']);
		})
			->add(new CheckRecordExists($this->vSitesPersistence, 'site_id'))
			->add(new CheckRecordExists($this->vUsersPersistence, 'user_id'));
	}
	
	public function list(Request $request, Response $response, $args): Response {	
		global $log;	
		$sort = (array) $request->getAttribute('sort');
		try {
			$records = $this->vSitesUsersPersistence->list(
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
		if (!isset($params['username'])) {
			$errors[] = 'username must be set';
		}
		if (!isset($params['role'])) {
			$errors[] = 'role must be set';
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
			$this->sitesUsersPersistence->create($params, [
				'site_id' => $args['site_id'],
				'user_id' => $args['user_id']
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
			$record = $this->vSitesUsersPersistence->get($args);
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
			$this->sitesUsersPersistence->update($args, $params);
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
			$rowsAffected = $this->sitesUsersPersistence->delete($args);
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
