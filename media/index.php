<?php

use Elgg\Application;
use Elgg\EntityDirLocator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

$autoload_root = dirname(dirname(dirname(__DIR__)));
if (!is_file("$autoload_root/vendor/autoload.php")) {
	$autoload_root = dirname(dirname(dirname($autoload_root)));
}
require_once "$autoload_root/vendor/autoload.php";

list($guid, $type, $filename) = explode('/', trim($_GET['__uri'], '/'));
$guid = (int) $guid;

if (!$guid) {
	$response = new Response('', Response::HTTP_NOT_FOUND);
	$response->send();
}

$last_cache = empty($_GET['lastcache']) ? 0 : (int) $_GET['lastcache']; // icontime
// If is the same ETag, content didn't changed.
$etag = $last_cache . $guid;
if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == "\"$etag\"") {
	$response = new Response('', Response::HTTP_NOT_MODIFIED);
	$response->send();
}

// @todo: validate hmac
$hmac = $_GET['hmac'];

$data_root = Application::getDataPath();
$locator = new EntityDirLocator($guid);

$full_path = "{$data_root}{$locator->getPath()}media/{$type}/{$filename}";
if (!file_exists($full_path)) {
	header("HTTP/1.1 404 Not Found");
	exit;
}

$contents = file_get_contents($full_path);

$response = new Response($contents);
$d = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, basename($full_path));
$response->headers->set('Content-Disposition', $d);
$response->headers->set('Content-Type', 'image/jpeg');
$response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', strtotime("+6 months")));
$response->headers->set('ETag', $etag);
$response->send();
