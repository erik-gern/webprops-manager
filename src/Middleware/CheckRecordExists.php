<?php

namespace PropMgr\Middleware;

use PropMgr\JsonResponse;
use PropMgr\DBPersistence;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Slim\Psr7\Response as SlimResponse;
use PDO;

class CheckRecordExists
{
	protected DBPersistence $persistence;
	protected string $idKey;
	public function __construct(DBPersistence $persistence, string $idKey) {
		$this->persistence = $persistence;
		$this->idKey = $idKey;
	}
	
	public function __invoke(Request $request, RequestHandler $handler) {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $id = $route->getArgument($this->idKey);

		$count = $this->persistence->count($id);
		if ($count == 0) {
			$response = new SlimResponse();
			$response = $response->withStatus(404);
			$response->getBody()->write((string) new JsonResponse([
				'status' => 'error',
				'error' => 'Record does not exist',
			]));
			return $response;
		}		
		$response = $handler->handle($request);
		return $response;
	}
}