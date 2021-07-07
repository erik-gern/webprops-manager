<?php

namespace PropMgr\Routes;

use PropMgr\DBPersistence;
use PropMgr\SqlHelper;
use PropMgr\JsonContentHandler;
use PropMgr\JsonResponse;
use PropMgr\Middleware\CheckRecordExists;
use PropMgr\Middleware\CanonicalizeSort;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;
use Slim\Psr7\Response as SlimResponse;
use PDO;

class NodeModulesGroup
{
	public function __construct($db) {
		global $log;
		$this->db = $db;
		$this->vSitesPersistence = new DBPersistence(
			$this->db, 
			'vNodeModules', 
			'id',
			[
				'title',
				'homepage_url',
				'description',
				'repo_url',
				'repo_type',
				'is_wp_dependency',
				'author_name',
				'author_emailaddress',
				'author_url',
				'license',
				'parents_count',
				'children_count',
				'search_text',
			]
		);
		$this->vSitesPersistence->addLogger($log);
		$this->sitesPersistence = new DBPersistence(
			$this->db, 
			'NodeModules', 
			'id',
			[
				'title',
				'homepage_url',
				'description',
				'repo_url',
				'repo_type',
				'is_wp_dependency',
				'author_name',
				'author_emailaddress',
				'author_url',
				'license',
			]
		);
		$this->sitesPersistence->useSoftDelete();
		$this->sitesPersistence->addLogger($log);
	}
	
	public function __invoke($ctx) {
		// list
		$ctx->get('', [$this, 'list'])
			->add(new CanonicalizeSort('sort'));
		
		// add
		$ctx->post('', [$this, 'add'])
			->add([$this, 'validate']);
		
		// view, edit, delete
		$ctx->group('/{id}', function($group){
			$group->get('', [$this, 'get']);
			$group->put('', [$this, 'update'])
				->add([$this, 'validate']);
			$group->delete('', [$this, 'delete']);
		})->add(new CheckRecordExists($this->vSitesPersistence, 'id'));

	}
	
	public function validate(Request $request, RequestHandler $handler): Response {				
		$data = $request->getParsedBody();
		$errors = [];
		if (empty($data['title'])) {
			$errors[] = 'Title is required';
		}
		if (empty($data['homepage_url'])) {
			$errors[] = 'Homepage URL is required';
		}

		if (count($errors) > 0) {
			$response = new SlimResponse();
			$response = $response->withStatus(400);
			$response->getBody()->write((string) new JsonResponse([
				'status' => 'error',
				'error' => 'Invalid data',
				'errors' => $errors,
			]));
			return $response;		
		}
		
		$response = $handler->handle($request);
		return $response;

	}
	
	public function list(Request $request, Response $response): Response {
		$attrs = $request->getQueryParams();
		$where = [];
		if (!empty($attrs['q'])) {
			$where['search_text'] = ['LIKE', '%'.$attrs['q'].'%'];
		}
		
		$sort = (array) $request->getAttribute('sort');
		$records = $this->vSitesPersistence->list($where, $sort);
		if ($records === false) {
			$response = $response->withStatus(500);
			$response->getBody()->write((string) new JsonResponse([
				'error' => 'There was a problem listing this resource',
			]));
			return $response;			
		}
		$response->getBody()->write((string) new JsonResponse([
			'records' => $records,
		]));
		return $response;		
	}
	
	public function get(Request $request, Response $response, $args): Response {	
		$record = $this->vSitesPersistence->get(intval($args['id']));
		if ($record === false) {
			$response = $response->withStatus(500);
			$response->getBody()->write((string) new JsonResponse([
				'error' => 'There was a problem getting this resource',
			]));
			return $response;
		}
		$response->getBody()->write((string) new JsonResponse([
			'record' => $record,
		]));	
		return $response;		
	}
	
	public function add(Request $request, Response $response, $args): Response {
		global $log;
		
		$site = array_merge([
			'title' => null,
			'homepage_url' => null,
			'description' => null,
			'repo_url' => null,
			'repo_type' => null,
			'is_wp_dependency' => null,
			'author_name' => null,
			'author_emailaddress' => null,
			'author_url' => null,
			'license' => null,
		], $request->getParsedBody());
		
		// save data
		try {
			$id = $this->sitesPersistence->create($site);
		}
		catch (any $e) {
			$log->error($e);
			$response = $response->withStatus(500);
			$response->getBody()->write((string) new JsonResponse([
				'status' => 'error',
				'error' => 'Error inserting record',
			]));
			return $response;	
		}
		
		$response = $response->withStatus(201);
		$response->getBody()->write((string) new JsonResponse([
			'status' => 'success',
			'id' => $id,
		]));
		return $response;	
	}
	
	public function update(Request $request, Response $response, $args): Response {
		
		$site = array_merge([
			'title' => null,
			'homepage_url' => null,
			'description' => null,
			'repo_url' => null,
			'repo_type' => null,
			'is_wp_dependency' => null,
			'author_name' => null,
			'author_emailaddress' => null,
			'author_url' => null,
			'license' => null,
		], $request->getParsedBody());
		
		// save data
		try {
			$this->sitesPersistence->update($args['id'], $site);
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
		$affectedRows = $this->sitesPersistence->delete($args['id']);
		$response->getBody()->write((string) new JsonResponse([
			'status' => 'success',
			'affected_rows' => $affectedRows,
		]));
		return $response;
	}

}
