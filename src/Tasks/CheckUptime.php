<?php

namespace PropMgr\Tasks;

use PropMgr\Tasks\Actions\SendHeadRequestToUrl;

class CheckUptime extends Task
{	
	public function getDefaultState(array $args): array {
		return [
			'urls' => array_combine(
				$args, 
				array_fill(0, count($args), null)
			),
		];
	}
	
	public function doNext() {
		$this->is_started = true;
		$foundNextUrl = false;
		$urls = array_keys($this->state['urls']);
		foreach ($urls as $url) {
			if (empty($this->state['urls'][$url])) {
				$foundNextUrl = true;
				$action = new SendHeadRequestToUrl();
				$this->state['urls'][$url] = $action($url);				
				break;
			}
		}
		if ($foundNextUrl == false) {
			$this->is_finished = true;
		}

	}
}