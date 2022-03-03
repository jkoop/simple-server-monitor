<?php

function getDataForHost(string $hostname): array {
	// get name of latest file
	$filename = glob('data/' . $hostname . '/*');

	if (count($filename) === 0) {
		return [];
	}

	rsort($filename);
	$filename = $filename[0];

	$loadAverages = array_map(function ($a) use ($hostname) {
		$a = trim(preg_replace('/\s+/', ' ', $a));

		$matches = [];
		preg_match('/^([0-9:]+) up (.*), (\d+) users?, load average: ([0-9\.]+), ([0-9\.]+), ([0-9\.]+)$/m', $a, $matches);

		return [
			'hostname' => $hostname,
			'time' => $matches[1],
			'uptime' => $matches[2],
			'users' => $matches[3],
			'load' => [
				'1' => $matches[4],
				'5' => $matches[5],
				'15' => $matches[6],
			],
		];
	}, explode("\n", file_get_contents($filename)));

	usort($loadAverages, function ($a, $b) {
		return strcmp($a['time'], $b['time']);
	});

	return $loadAverages;
}

include('hosts.php');

if (!isset($hosts)) {
	http_response_code(500);
	throw new Error('No hosts defined');
}

$data = [];

foreach ($hosts as $hostname => $cores) {
	$data[$hostname] = getDataForHost($hostname);
	$data[$hostname] = array_map(function ($record) {
		$record['time'] = substr($record['time'], 0, -3);
		return $record;
	},	$data[$hostname]);
}

date_default_timezone_set('Etc/UTC');

// foreach ($data as $host) {
// 	foreach ($host as $record) {
// 		if ($record['time'] === null) continue;

// 		$chartData[] = [
// 			'x' => strtotime(date('Y-m-d ') . $record['time']) * 1000,
// 			$record['hostname'] . '-1m' => $record['load']['1'] / $hosts[$record['hostname']],
// 			$record['hostname'] . '-5m' => $record['load']['5'] / $hosts[$record['hostname']],
// 			$record['hostname'] . '-15m' => $record['load']['15'] / $hosts[$record['hostname']],
// 		];
// 	}
// }

$chartData = [
	1 => [],
	5 => [],
	15 => [],
];

foreach ($data as $host) {
	foreach ($host as $record) {
		if ($record['time'] === null) continue;

		$time = strtotime(date('Y-m-d ') . $record['time']);

		foreach ([1, 5, 15] as $avg) {
			$chartData[$avg][$time][] = $record['load'][$avg] / $hosts[$record['hostname']];
		}
	}
}

$outputData = [];

foreach ($chartData as $avg => $avgData) {
	foreach ($avgData as $time => $data) {
		$data = array_sum($data) / count($data);
		$data = round($data, 4);

		$outputData[] = [
			'x' => $time * 1000,
			'avg-liddell-' . $avg . 'm' => $data,
		];
	}
}

header('Content-Type: application/json');
echo json_encode([
	'hosts' => ['avg-liddell'],
	'chart' => $outputData,
]);
