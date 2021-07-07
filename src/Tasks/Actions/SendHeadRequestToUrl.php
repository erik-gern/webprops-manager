<?php

namespace PropMgr\Tasks\Actions;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;

class SendHeadRequestToUrl extends Action
{
	public function __invoke($url) {
		try {
			$client = new HttpClient();
			$response = $client->head($url);
			return $response->getStatusCode();
		}
		catch (ConnectException $e) {
			return $e->getMessage();
		}
		catch (any $e) {
			throw $e;
		}
	}
}