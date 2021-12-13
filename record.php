<?php

// cli only
if (php_sapi_name() != 'cli') {
	die('This script can only be run from the command line.');
}

include('lib/common.php');

foreach ($hosts as $hostname => $cores) {
	echo $hostname . "...\n";

	$line = exec('timeout 15 ssh ' . escapeshellarg($hostname) . ' TZ=Etc/UTC cat /proc/loadavg');

	if ($line === '') echo "!!! Failed to connect to $hostname. Maybe you need to run\n    ssh-copy-id " . escapeshellarg($hostname) . "\n";

	$time = time();
	$matches = [];
	preg_match('/^\s*([0-9\.]+)\s*([0-9\.]+)\s*([0-9\.]+)/m', $line, $matches);

	db()->exec('INSERT INTO `data` (`time`, `hostname`, `topic`, `normalizer`, `value_raw`, `value_normalized`) VALUES (:time, :hostname, :topic, :normalizer, :valueRaw, :valueNormalized)', [
		'time' => $time,
		'hostname' => $hostname,
		'topic' => 'loadavg-1min',
		'normalizer' => $cores,
		'valueRaw' => $matches[1] ?? null,
		'valueNormalized' => ($matches[1] ?? null) / $cores,
	]);

	db()->exec('INSERT INTO `data` (`time`, `hostname`, `topic`, `normalizer`, `value_raw`, `value_normalized`) VALUES (:time, :hostname, :topic, :normalizer, :valueRaw, :valueNormalized)', [
		'time' => $time,
		'hostname' => $hostname,
		'topic' => 'loadavg-5min',
		'normalizer' => $cores,
		'valueRaw' => $matches[2] ?? null,
		'valueNormalized' => ($matches[2] ?? null) / $cores,
	]);

	db()->exec('INSERT INTO `data` (`time`, `hostname`, `topic`, `normalizer`, `value_raw`, `value_normalized`) VALUES (:time, :hostname, :topic, :normalizer, :valueRaw, :valueNormalized)', [
		'time' => $time,
		'hostname' => $hostname,
		'topic' => 'loadavg-15min',
		'normalizer' => $cores,
		'valueRaw' => $matches[3] ?? null,
		'valueNormalized' => ($matches[3] ?? null) / $cores,
	]);
}

echo "Done! Exiting\n";
