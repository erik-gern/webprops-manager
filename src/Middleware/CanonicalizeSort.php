<?php

namespace PropMgr\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

class CanonicalizeSort
{
	protected string $key;
	public function __construct(string $key) {
		$this->key = $key;
	}

	public function __invoke(Request $request, RequestHandler $handler) {
		
		$attrs = $request->getQueryParams();	
		if (!array_key_exists($this->key, $attrs)) {
			$sortRaw = '';
		}
		else {
			$sortRaw = $attrs[$this->key];
		}

		// split into components and normalize direction	
		$sort = array_map(function($part){
			$parts = explode(':', $part);
			return [
				$parts[0], 
				(empty($parts[1]) ? 'ASC' : strtoupper($parts[1])),
			];
		}, explode(',', $sortRaw));
		
		// remove empties and non-valid
		$sort = array_filter($sort, function($sortPair){
			if ($sortPair[1] != 'ASC' && $sortPair[1] != 'DESC') {
				return false;
			}
			if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]+$/', $sortPair[0]) != 1) {
				return false;
			}
			return true;
		});
		
		$request = $request->withAttribute('sort', $sort);
				
		$response = $handler->handle($request);
		return $response;	
			
	}
	
}