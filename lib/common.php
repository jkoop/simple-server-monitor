<?php

include(__DIR__ . '/../hosts.php');
include(__DIR__ . '/sqlite.php');

// require hosts.php
if (!isset($hosts)) {
	http_response_code(500);
	throw new Error('No hosts defined (hosts.php)');
}

function getDataForTopic(string $topic): array {
	return db()->queryAll('SELECT * FROM `data` WHERE `topic` = :topic AND `time` > :aDayAgo ORDER BY `time` ASC', [
		'topic' => $topic,
		'aDayAgo' => time() - 60 * 5, // * 60 * 24,
	]);
}
