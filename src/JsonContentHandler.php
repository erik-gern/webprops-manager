<?php

namespace PropMgr;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class JsonContentHandler
{	
	public function __invoke(Request $request, RequestHandler $handler): Response
	{
		$response = $handler->handle($request);
		$response->withHeader('Content-Type', 'application/json');
		$response->withheader('X-Testing', 'foo bar');
		return $response;
	}	
}