<?php

namespace PropMgr\NPM;

class PackageHelper
{
	public static function getDetails($pkg, $key=null) {
		$command = ['npm', 'view', escapeshellarg($pkg)];
		if (!empty($key)) {
			$command[] = escapeshellarg($key);
		}
		$command[] = '--json';
		$outJson = shell_exec(implode(' ', $command));
		$out = json_decode($outJson, true);
		$out = self::normalizePackageDetails($out);
		return $out;		
	}
	
	public static function normalizePackageDetails($in) {
		$out = array_merge([
			'name' => '',
			'description' => '',
			'author' => [
				'name' => '',
				'email' => '',
				'url' => '',
			],
		], $in);
		// turn author string into associative array
		if (!is_array($out['author'])) {
			$authorName = $out['author'];
			$authorEmailAddress = '';
			$authorUrl = '';
			// author with email, link
			if (preg_match('/^(.+) \<([^\>]+)\> \(([^\)]+)\)$/', $authorName, $matches)) {
				$authorName = $matches[1];
				$authorEmailAddress = $matches[2];
				$authorUrl = $matches[3];
			}
			// author with link, email
			elseif (preg_match('/^(.+) \(([^\)]+)\) \<([^\>]+)\>$/', $authorName, $matches)) {
				$authorName = $matches[1];
				$authorUrl = $matches[2];
				$authorEmailAddress = $matches[3];
			}			
			// author with email
			elseif (preg_match('/^(.+) \<([^\>]+)\>$/', $authorName, $matches)) {
				$authorName = $matches[1];
				$authorEmailAddress = $matches[2];
			}
			// author with link
			elseif (preg_match('/^(.+) \(([^\)]+)\)$/', $authorName, $matches)) {
				$authorName = $matches[1];
				$authorUrl = $matches[2];
			}		
			$out['author'] = [
				'name' => $authorName,
				'email' => $authorEmailAddress,
				'url' => $authorUrl,
			];
		}
		return $out;
	}
}