<?php

require __DIR__.'/../../vendor/autoload.php';

use PropMgr\Tasks\CheckUptime;

$task = new CheckUptime(
	1,
	time(),
	[
		'https://www.evopayments.com',
		'https://www.evopayments.us',
		'https://foo.bar',
	]
);
$i = 0;
while (!$task->getIsFinished()) {
	echo "Iteration $i".chr(10);
	var_dump($task); echo chr(10);
	$task->doNext();
}
echo "Done".chr(10);
var_dump($task); echo chr(10);
