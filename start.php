<?php

/**
 * Media API
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2015, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', 'elgg_media_init');

/**
 * Initialize the plugin
 * @return void
 */
function elgg_media_init() {

	elgg_define_js('cropper', ['src' => elgg_get_simplecache_url('cropper/cropper.min.js'), 'deps' => ['jquery']]);

	elgg_register_simplecache_view('media.css');
	elgg_register_css('media', elgg_get_simplecache_url('media.css'));

	elgg_register_action('media/upload', __DIR__ . '/actions/media/upload.php');

	elgg_register_plugin_hook_handler('media:icon', 'user', 'Elgg\Values::getTrue');
	elgg_register_plugin_hook_handler('media:cover', 'user', 'Elgg\Values::getTrue');
	elgg_register_plugin_hook_handler('media:skyscraper', 'user', 'Elgg\Values::getTrue');


	elgg_register_plugin_hook_handler('route', 'avatar', 'elgg_media_route_avatar');
	elgg_unregister_plugin_hook_handler('entity:icon:url', 'user', 'profile_set_icon_url');
	elgg_unregister_plugin_hook_handler('entity:icon:url', 'user', 'user_avatar_hook');

	elgg_register_plugin_hook_handler('entity:icon:url', 'all', 'elgg_media_url_handler');
	elgg_register_plugin_hook_handler('entity:cover:url', 'all', 'elgg_media_url_handler');
	elgg_register_plugin_hook_handler('entity:skyscraper:url', 'all', 'elgg_media_url_handler');

	elgg_register_plugin_hook_handler('entity:icon:sizes', 'all', 'elgg_media_icon_sizes_handler', 1000);
	elgg_register_plugin_hook_handler('entity:cover:sizes', 'all', 'elgg_media_cover_sizes_handler', 1000);
	elgg_register_plugin_hook_handler('entity:skyscraper:sizes', 'all', 'elgg_media_skyscraper_sizes_handler', 1000);

	elgg_register_page_handler('media', 'elgg_media_page_handler');

	$cover_sizes = [
		'small' => ['w' => 480, 'h' => 480 * 2.7, 'square' => false, 'upscale' => true],
		'medium' => ['w' => 960, 'h' => 960 * 2.7, 'square' => false, 'upscale' => true],
		'large' => ['w' => 1920, 'h' => 1920 * 2.7, 'square' => false, 'upscale' => true],
		'master' => ['w' => 1920, 'h' => 1920, 'square' => false, 'upscale' => true],
	];
	elgg_set_config('cover_sizes', $cover_sizes);

	$skyscraper_sizes = [
		'small' => ['w' => 100, 'h' => 400, 'square' => false, 'upscale' => true],
		'medium' => ['w' => 200, 'h' => 800, 'square' => false, 'upscale' => true],
		'large' => ['w' => 400, 'h' => 1200, 'square' => false, 'upscale' => true],
		'master' => ['w' => 1200, 'h' => 1200, 'square' => false, 'upscale' => true],
	];
	elgg_set_config('skyscraper_sizes', $skyscraper_sizes);
}
