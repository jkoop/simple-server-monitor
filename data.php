<?php

function getDataForHost(string $hostname): array {
	// get name of latest file
	$filename = glob('data/' . $hostname . '/*');

	if (count($filename) === 0) {
		return [];
	}

	sort($filename);
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
}

$chartData = [];

date_default_timezone_set('Etc/UTC');

foreach ($data as $host) {
	foreach ($host as $record) {
		if ($record['time'] === null) continue;

		$chartData[] = [
			'x' => strtotime(date('Y-m-d ') . $record['time']) * 1000,
			$record['hostname'] . '-1m' => $record['load']['1'] / $hosts[$record['hostname']],
			$record['hostname'] . '-5m' => $record['load']['5'] / $hosts[$record['hostname']],
			$record['hostname'] . '-15m' => $record['load']['15'] / $hosts[$record['hostname']],
		];
	}
}

header('Content-Type: application/json');
echo json_encode([
	'hosts' => array_keys($hosts),
	'chart' => $chartData
]);
