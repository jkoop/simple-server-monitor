<?php

include('lib/common.php');

$data = getDataForTopic($_GET['topic'] ?? die('No topic specified'));

$chartData = [];

date_default_timezone_set('Etc/UTC');

foreach ($data as $record) {
	$time = $record->time;
	$hostname = $record->hostname;
	$topic = $record->topic;
	$value = $record->value_normalized;

	if (!isset($chartData[$time])) $chartData[$time] = [];

	$chartData[$time]['x'] = $time * 1000; // convert to milliseconds for javascript
	$chartData[$time][$hostname . '/' . $topic] = $value;
}

header('Content-Type: application/json');
echo json_encode([
	'hosts' => array_keys($hosts),
	'chart' => array_values($chartData),
]);
