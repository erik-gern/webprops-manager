<?php

require './vendor/autoload.php';

use PropMgr\NPM\PackageHelper;

use GuzzleHttp\Client as HttpClient;

$db = new PDO('sqlite:'.__DIR__.'/../../data.db');

$npmPackageUrl = 'https://core.trac.wordpress.org/browser/trunk/package.json?format=txt';

$client = new HttpClient();
$response = $client->request('GET', $npmPackageUrl);
$npmPackageJson = json_decode($response->getBody(), true);
$npmPackages = array_keys($npmPackageJson['dependencies']);

foreach ($npmPackages as $pkg) {
	echo $pkg.':'.chr(10);

	// see if the node module is already recorded
	$sql = 'SELECT COUNT(*) FROM NodeModules WHERE title=:title';
	$stmt = $db->prepare($sql);
	if ($stmt === false) {
		echo '  Failed to prepare COUNT(*) statement: '.implode(':', $db->errorInfo()).chr(10);
		continue;
	}
	$stmt->bindValue(':title', $pkg, PDO::PARAM_STR);
	$res = $stmt->execute();
	if ($res === false) {
		echo '  Failed to execute COUNT(*) statement: '.implode(':', $db->errorInfo()).chr(10);
		continue;
	}
	$count = intval($stmt->fetchColumn());
	if ($count > 0) {
		echo "  Node module $pkg already recorded, skipping.".chr(10);
		continue;
	}

	// get package data
	$pkgData = PackageHelper::getDetails($pkg);
	echo "  Title: {$pkgData['name']}".chr(10);
	echo "  Homepage: {$pkgData['homepage']}".chr(10);
	
	// insert
	$sql = <<<EOT
		INSERT INTO NodeModules (
			title,
			description,
			homepage_url,
			repo_url,
			repo_type,
			author_name,
			author_emailaddress,
			author_url,
			license,
			is_wp_dependency
		)
		VALUES (
			:title,
			:description,
			:homepage_url,
			:repo_url,
			:repo_type,
			:author_name,
			:author_emailaddress,
			:author_url,
			:license,
			:is_wp_dependency
		)
EOT;
	$stmt = $db->prepare($sql);
	if ($stmt === false) {
		echo '  Failed to prepare INSERT statement: '.implode(':', $db->errorInfo()).chr(10);
		continue;
	}
	$stmt->bindValue(':title', $pkgData['name'], PDO::PARAM_STR);
	$stmt->bindValue(':description', $pkgData['description'], PDO::PARAM_STR);
	$stmt->bindValue(':homepage_url', $pkgData['homepage'], PDO::PARAM_STR);
	$stmt->bindValue(':repo_url', $pkgData['repository']['url'], PDO::PARAM_STR);
	$stmt->bindValue(':repo_type', $pkgData['repository']['type'], PDO::PARAM_STR);
	if (isset($pkgData['author'])) {
		if (is_array($pkgData['author'])) {
			$stmt->bindValue(':author_name', $pkgData['author']['name'], PDO::PARAM_STR);
			$stmt->bindValue(':author_emailaddress', $pkgData['author']['email'], PDO::PARAM_STR);
			$stmt->bindValue(':author_url', $pkgData['author']['url'], PDO::PARAM_STR);
		}
		else {
			// guess these values with regexp matching
			$authorName = $pkgData['author'];
			$authorEmailAddress = '';
			$authorUrl = '';
			// author with email, link
			if (preg_match('/^(.+) \<([^\>]+)\> \(([^\)]+)\)$/', $authorName, $matches)) {
				$authorName = $matches[1];
				$authorEmailAddress = $matches[2];
				$authorUrl = $matches[3];
			}
			// author with link, email
			if (preg_match('/^(.+) \(([^\)]+)\) \<([^\>]+)\>$/', $authorName, $matches)) {
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
			$stmt->bindValue(':author_name', $authorName, PDO::PARAM_STR);
			$stmt->bindValue(':author_emailaddress', $authorEmailAddress, PDO::PARAM_STR);
			$stmt->bindValue(':author_url', $authorUrl, PDO::PARAM_STR);
		}
	}
	else {
		$stmt->bindValue(':author_name', '', PDO::PARAM_STR);
		$stmt->bindValue(':author_emailaddress', '', PDO::PARAM_STR);
		$stmt->bindValue(':author_url', '', PDO::PARAM_STR);
	}
	if (isset($pkgData['license'])) {
		$stmt->bindValue(':license', $pkgData['license'], PDO::PARAM_STR);
	}
	else {
		$stmt->bindValue(':license', 'N/A', PDO::PARAM_STR);
	}
	$stmt->bindValue(':is_wp_dependency', 1, PDO::PARAM_INT);
	
	$res = $stmt->execute();
	if ($res === false) {
		echo '  Failed to execute INSERT statement: '.implode('', $db->errorInfo()).chr(10);
		continue;
	}
	echo '  Inserted.'.chr(10);	
}
