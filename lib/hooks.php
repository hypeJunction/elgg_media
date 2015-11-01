<?php

/**
 * Routes avatar edit page
 * /avatar/edit/<guid>
 *
 * @param string $hook   "route"
 * @param string $type   "avatar"
 * @param array  $return Identifier and segments
 * @return array
 */
function elgg_media_route_avatar($hook, $type, $return) {

	$identifier = elgg_extract('handler', $return);
	$segments = elgg_extract('segments', $return);

	if ($identifier == 'avatar' && $segments[0] == 'edit') {
		$user = get_user_by_username(elgg_extract(1, $segments)) ? : elgg_get_logged_in_user_entity();
		return [
			'identifier' => 'media',
			'segments' => [
				$user->guid,
				'edit',
				'icon',
			],
		];
	}

	return $return;
}

/**
 * Icon URL
 *
 * @param type $hook
 * @param type $hook_type
 * @param type $return
 * @param type $params
 * @return type
 */
function elgg_media_url_handler($hook, $hook_type, $return, $params) {

	$entity = elgg_extract('entity', $params);
	$size = elgg_extract('size', $params, 'medium');
	$type = elgg_extract('type', $params, 'icon');
	$ext = elgg_extract('ext', $params, 'jpg');

	$filename = false;

	if ($size == 'original') {
		$filename = $entity->{"{$type}_originalfilename"};
	} else if (elgg_get_media_file($entity, $type, $size, $ext)) {
		$filename = "$size.$ext";
	}

	if ($filename) {
		$url = elgg_normalize_url("/mod/elgg_media/media/$entity->guid/$type/$filename");
		// @todo: add hmac
		return elgg_http_add_url_query_elements($url, [
			'lastcache' => $entity->{"{$type}_time_created"},
			'hmac' => 'todo',
		]);
	}
}

function elgg_media_icon_sizes_handler($hook, $type, $return, $params) {
	// make sure we always have sitewide sizes in config
	return array_merge((array) elgg_get_config('icon_sizes'), (array) $return);
}

function elgg_media_cover_sizes_handler($hook, $type, $return, $params) {
	// make sure we always have sitewide sizes in config
	return array_merge((array) elgg_get_config('cover_sizes'), (array) $return);
}

function elgg_media_skyscraper_sizes_handler($hook, $type, $return, $params) {
	// make sure we always have sitewide sizes in config
	return array_merge((array) elgg_get_config('skyscraper_sizes'), (array) $return);
}