<?php

require './vendor/autoload.php';

use Seld\JsonLint\JsonParser;

function json_clean($dirty) {
	// remove extra whitespace
	$clean = trim($dirty);
	// replace single quotes with double quotes
	$clean = str_replace("'", '"', $dirty);
	// quote unquote object keys
	$clean = preg_replace_callback('/([^\'"])([a-zA-Z0-9\-_\@\$]+):/', function($match){
		return $match[1].'"'.$match[2].'":';
	}, $clean);
	return $clean;
}

$npmPackage = 'brfs';
$command = "npm view $npmPackage dependencies";
$jsonDirty = shell_exec($command);
$jsonClean = json_clean($jsonDirty);
$deps = (new JsonParser())->parse($jsonClean, JsonParser::PARSE_TO_ASSOC);
var_dump($deps);
$depNames = array_keys($deps);
foreach ($depNames as $depName) {
	echo $depName.chr(10);
	$command = "npm view $depName author.name";
	echo shell_exec($command).chr(10);
}