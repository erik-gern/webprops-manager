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

class SitesGroup
{
	public function __construct($db) {
		global $log;
		$this->db = $db;
		$this->vSitesPersistence = new DBPersistence(
			$this->db, 
			'vSites', 
			'id',
			[
				'title',
				'url',
				'admin_url',
				'host',
				'description',
				'php_version_major',
				'php_version_minor',
				'php_version_patch',
				'wordpress_version_major',
				'wordpress_version_minor',
				'wordpress_version_patch',
				'has_2factor',
				'ssl_expiry',
				'ssl_provider',
				'email_from_name',
				'email_from_address',
				'admin_address',
				'last_reviewed',
				'plugins_installed',
				'plugins_active',
				'themes_installed',
				'primary_theme',
				'users_count',
				'php_version',
				'wordpress_version',
				'lastreview_isexpired',
				'lastreview_dayssince',
				'search_text',
			]
		);
		$this->vSitesPersistence->addLogger($log);
		$this->sitesPersistence = new DBPersistence(
			$this->db, 
			'Sites', 
			'id',
			[
				'title',
				'url',
				'admin_url',
				'host',
				'description',
				'php_version_major',
				'php_version_minor',
				'php_version_patch',
				'wordpress_version_major',
				'wordpress_version_minor',
				'wordpress_version_patch',
				'has_2factor',
				'ssl_expiry',
				'ssl_provider',
				'email_from_name',
				'email_from_address',
				'admin_address',
				'last_reviewed'
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
		if (empty($data['url'])) {
			$errors[] = 'URL is required';
		}
		if (empty($data['host'])) {
			$errors[] = 'Host is required';
		}
		if (!empty($data['php_version_major']) && !is_numeric($data['php_version_major'])) {
			$errors[] = 'PHP version major must be an integer';
		}
		if (!empty($data['php_version_minor']) && !is_numeric($data['php_version_minor'])) {
			$errors[] = 'PHP version minor must be an integer';
		}
		if (!empty($data['php_version_patch']) && !is_numeric($data['php_version_patch'])) {
			$errors[] = 'PHP version patch must be an integer';
		}

		if (!empty($data['wordpress_version_major']) && !is_numeric($data['wordpress_version_major'])) {
			$errors[] = 'WordPress version major must be an integer';
		}
		if (!empty($data['wordpress_version_minor']) && !is_numeric($data['wordpress_version_minor'])) {
			$errors[] = 'WordPress version minor must be an integer';
		}
		if (!empty($data['wordpress_version_patch']) && !is_numeric($data['wordpress_version_patch'])) {
			$errors[] = 'WordPress version patch must be an integer';
		}

		if (isset($data['has_2factor']) && (
			intval($data['has_2factor']) != 0 && intval($data['has_2factor']) != 1) 
		) {
			$errors[] = 'Has 2Factor must be either 0 or 1';
		}
		if (!empty($data['ssl_expiry']) && preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/', $data['ssl_expiry']) != 1) {
			$errors[] = 'SSL Expiry must be a date in the format yyyy-mm-dd';
		}
		if (!empty($data['last_reviewed']) && preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/', $data['last_reviewed']) != 1) {
			$errors[] = 'Last reviewed must be a date in the format yyyy-mm-dd';
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
			'url' => null,
			'host' => null,
			'description' => null,
			'php_version_major' => null,
			'php_version_minor' => null,
			'php_version_patch' => null,
			'wordpress_version_major' => null,
			'wordpress_version_minor' => null,
			'wordpress_version_patch' => null,
			'has_2factor' => null,
			'ssl_expiry' => null,
			'ssl_provider' => null,
			'email_from_name' => null,
			'email_from_address' => null,
			'admin_address' => null,
			'last_reviewed' => null,
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
			'url' => null,
			'host' => null,
			'description' => null,
			'php_version_major' => null,
			'php_version_minor' => null,
			'php_version_patch' => null,
			'wordpress_version_major' => null,
			'wordpress_version_minor' => null,
			'wordpress_version_patch' => null,
			'has_2factor' => null,
			'ssl_expiry' => null,
			'ssl_provider' => null,
			'email_from_name' => null,
			'email_from_address' => null,
			'admin_address' => null,
			'last_reviewed' => null,
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
