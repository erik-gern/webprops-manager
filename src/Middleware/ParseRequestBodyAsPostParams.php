<?php

namespace PropMgr\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

class ParseRequestBodyAsPostParams
{
    public function __construct() {
        
    }
	public function __invoke(Request $request, RequestHandler $handler) {
		$contentType = $request->getHeaderLine('Content-Type');
        if (strstr($contentType, 'application/x-www-form-urlencoded')) {
            $pairs = array_map(
            	function($piece){
            		return array_map(
            			function($str){ return urldecode($str); }, 
            			explode('=', $piece)
            		);
            	}, 
            	explode('&', file_get_contents('php://input'))
            );
            $pairKeys = array_map(function($p){ return $p[0]; }, $pairs);
            $pairVals = array_map(
            	function($p){ 
            		return isset($p[1]) ? $p[1] : null; 
            	}, 
            	$pairs
            );
            $contents = array_combine($pairKeys, $pairVals);
            $request = $request->withParsedBody($contents);
        }
        return $handler->handle($request);		
	}
}