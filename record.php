<?php

// cli only
if (php_sapi_name() != 'cli') {
	die('This script can only be run from the command line.');
}

chdir(__DIR__);

include('hosts.php');

if (!isset($hosts)) {
	throw new Error('No hosts defined');
}

foreach ($hosts as $hostname => $cores) {
	echo $hostname . "...\n";

	if (!is_dir('data/' . $hostname)) {
		mkdir('data/' . $hostname);
	}

	$line = exec('timeout 15 ssh ' . escapeshellarg($hostname) . ' TZ=Etc/UTC uptime');

	if ($line === '') {
		echo "!!! Failed to connect to $hostname. Maybe you need to run\n    ssh-copy-id " . escapeshellarg($hostname) . "\n";
		continue;
	}

	$file = fopen('data/' . $hostname . '/' . date('Y-m-d'), 'a'); // write append
	fwrite($file, $line . "\n");
	fclose($file);
}

echo "Done! Exiting\n";
