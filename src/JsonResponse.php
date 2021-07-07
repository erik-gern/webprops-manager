<?php

namespace PropMgr;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class JsonResponse
{
	protected array $data;
	protected bool $prettyPrint;
	public function __construct(array $data, bool $prettyPrint=false) {
		$this->data = $data;
		$this->prettyPrint = $prettyPrint;
	}
	
	public function array_change_key_case_recursive(array $arr): array 
	{
		return array_map(function($e){
			if (is_array($e)) {
				$e = $this->array_change_key_case_recursive($e);
			}
			return $e;
		}, array_change_key_case($arr));
	}
	
	public function __toString(): string
	{
		$flags = 0;
		if ($this->prettyPrint) {
			$flags |= JSON_PRETTY_PRINT;
		}
		return json_encode(
			$this->array_change_key_case_recursive($this->data), 
			$flags
		);
	}	
}